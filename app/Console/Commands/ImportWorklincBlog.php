<?php

namespace App\Console\Commands;

use App\Domain\Content\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImportWorklincBlog extends Command
{
    protected $signature = 'worklinc:import-blog';

    protected $description = 'Import published WorkLinc blog posts and their images';

    private const API_URL = 'https://shop.worklinc.com/wp-json/wp/v2/posts';

    public function handle(): int
    {
        $posts = $this->fetchPosts();
        $postUrls = [];

        foreach ($posts as $post) {
            $sourceId = (int) ($post['id'] ?? 0);
            $slug = Str::slug((string) ($post['slug'] ?? ''));
            if ($sourceId < 1 || $slug === '') {
                throw new RuntimeException('WorkLinc returned a post without a valid ID or slug.');
            }
            $postUrls[(string) ($post['link'] ?? '')] = route('blog.show', $slug);
        }

        $imported = 0;
        $importedImages = [];
        $skippedImages = 0;
        foreach ($posts as $post) {
            $sourceId = (int) $post['id'];
            $slug = Str::slug((string) $post['slug']);
            $title = trim(html_entity_decode(strip_tags((string) ($post['title']['rendered'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $content = (string) ($post['content']['rendered'] ?? '');
            $excerpt = trim(html_entity_decode(strip_tags((string) ($post['excerpt']['rendered'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($title === '' || $content === '') {
                throw new RuntimeException("WorkLinc blog post {$sourceId} has no title or body.");
            }

            $featured = $post['_embedded']['wp:featuredmedia'][0] ?? [];
            $featuredUrl = (string) ($featured['source_url'] ?? '');
            $imageUrls = $this->extractImageUrls($content);
            if ($featuredUrl !== '') {
                array_unshift($imageUrls, $featuredUrl);
            }
            $imageUrls = array_values(array_unique($imageUrls));
            $imagePaths = [];

            foreach ($imageUrls as $sourceUrl) {
                try {
                    $imagePath = $this->saveImage($sourceUrl);
                } catch (RequestException $exception) {
                    if ($exception->response->status() !== 404 || $sourceUrl === $featuredUrl) {
                        throw $exception;
                    }

                    $this->warn("Skipped unavailable inline image in {$slug}: {$sourceUrl}");
                    $content = $this->removeUnavailableImage($content, $sourceUrl);
                    $skippedImages++;
                    continue;
                }

                $imagePaths[$sourceUrl] = $imagePath;
                $importedImages[$sourceUrl] = true;
                $content = str_replace($sourceUrl, url('/storage/media/'.$imagePath), $content);
            }

            foreach ($postUrls as $sourceUrl => $localUrl) {
                if ($sourceUrl !== '') {
                    $content = str_replace($sourceUrl, $localUrl, $content);
                }
            }

            $meta = $post['yoast_head_json'] ?? [];
            BlogPost::updateOrCreate(
                ['source_id' => $sourceId],
                [
                    'slug' => $slug,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'featured_image_path' => $imagePaths[$featuredUrl] ?? null,
                    'featured_image_alt' => trim((string) ($featured['alt_text'] ?? '')) ?: $title,
                    'author_name' => trim((string) ($post['_embedded']['author'][0]['name'] ?? '')) ?: 'WorkLinc',
                    'source_url' => $post['link'] ?? null,
                    'meta_title' => trim(strip_tags((string) ($meta['title'] ?? ''))) ?: $title,
                    'meta_description' => trim(strip_tags((string) ($meta['description'] ?? ''))) ?: Str::limit($excerpt, 300),
                    'is_published' => ($post['status'] ?? '') === 'publish',
                    'published_at' => $post['date'] ?? null,
                ],
            );
            $imported++;
            $this->line("Imported: {$title}");
        }

        $this->info("Imported {$imported} blog posts and ".count($importedImages).' unique images.');
        if ($skippedImages > 0) {
            $this->warn("Skipped {$skippedImages} unavailable inline images; their captions were retained.");
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchPosts(): array
    {
        $posts = [];
        $page = 1;
        $lastPage = 1;

        do {
            $response = Http::acceptJson()
                ->withUserAgent('WorkLinc blog importer')
                ->timeout(45)
                ->retry(3, 1000, fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response?->status() === 429))
                ->get(self::API_URL, ['per_page' => 100, 'page' => $page, '_embed' => 1])
                ->throw();
            $payload = $response->json();

            if (! is_array($payload) || ! array_is_list($payload)) {
                throw new RuntimeException("WorkLinc returned an invalid blog post list on page {$page}.");
            }

            foreach ($payload as $post) {
                if (! is_array($post)) {
                    throw new RuntimeException("WorkLinc returned an invalid blog post on page {$page}.");
                }
                $posts[] = $post;
            }

            $lastPage = max($page, (int) $response->header('X-WP-TotalPages', $page));
            $page++;
        } while ($page <= $lastPage);

        if ($posts === []) {
            throw new RuntimeException('WorkLinc returned no published blog posts.');
        }

        return $posts;
    }

    /**
     * @return list<string>
     */
    private function extractImageUrls(string $content): array
    {
        preg_match_all(
            '~https?://shop\.worklinc\.com/wp-content/uploads/[^"\'\s<>()]+~i',
            $content,
            $matches,
        );

        return array_map(
            fn (string $url): string => rtrim($url, ',;'),
            $matches[0],
        );
    }

    private function removeUnavailableImage(string $content, string $url): string
    {
        $quotedUrl = preg_quote($url, '~');
        $content = preg_replace(
            '~<img\b(?=[^>]*\bsrc=["\']'.$quotedUrl.'["\'])[^>]*>~i',
            '',
            $content,
        ) ?? $content;

        return str_replace($url, '', $content);
    }

    private function saveImage(string $url): string
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https' || strtolower((string) ($parts['host'] ?? '')) !== 'shop.worklinc.com') {
            throw new RuntimeException('A blog image URL is not on the WorkLinc HTTPS host.');
        }

        $disk = Storage::disk('media');
        $responseHash = hash('sha256', $url);
        $extension = strtolower(pathinfo((string) ($parts['path'] ?? ''), PATHINFO_EXTENSION));
        $path = 'blog/media/'.$responseHash.'.'.$extension;

        if ($disk->exists($path)) {
            return $path;
        }

        $encodedPath = '/'.implode('/', array_map(
            fn (string $segment): string => rawurlencode(rawurldecode($segment)),
            array_values(array_filter(explode('/', (string) ($parts['path'] ?? '')), fn (string $segment): bool => $segment !== '')),
        ));
        $requestUrl = 'https://shop.worklinc.com'.$encodedPath;
        if (isset($parts['query'])) {
            $requestUrl .= '?'.$parts['query'];
        }

        $response = Http::accept('image/*')
            ->withUserAgent('WorkLinc blog importer')
            ->timeout(60)
            ->retry(3, 1000, fn (Throwable $exception): bool => $exception instanceof ConnectionException
                || ($exception instanceof RequestException && $exception->response?->status() === 429))
            ->get($requestUrl)
            ->throw();

        $mimeType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => throw new RuntimeException("The blog image source returned an unsupported image type ({$mimeType})."),
        };
        $contents = $response->body();
        if ($contents === '' || strlen($contents) > 20 * 1024 * 1024) {
            throw new RuntimeException('A blog image is empty or exceeds the 20 MB import limit.');
        }

        $path = 'blog/media/'.$responseHash.'.'.$extension;
        if (! $disk->put($path, $contents)) {
            throw new RuntimeException("Unable to save blog image {$path}.");
        }

        return $path;
    }
}
