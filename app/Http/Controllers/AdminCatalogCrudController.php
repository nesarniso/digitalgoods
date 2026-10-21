<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Models\AttributeValue;
use App\Domain\Catalog\Services\AttributeService;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Services\BrandService;
use App\Domain\Catalog\Services\CategoryService;
use App\Domain\Commerce\Models\Coupon;
use App\Domain\Commerce\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCatalogCrudController extends Controller
{
    public function __construct(
        private readonly CategoryService $categories,
        private readonly BrandService $brands,
        private readonly CouponService $coupons,
        private readonly AttributeService $attributes,
    ) {}

    public function attributeCreate()
    {
        return view('admin.crud.attribute-form', ['attribute' => new Attribute]);
    }

    public function attributeStore(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:attributes,name']]);
        $this->attributes->create($data['name'], $request->user());
        return redirect()->route('admin.attributes')->with('success', 'Attribute created.');
    }

    public function attributeEdit(Attribute $attribute)
    {
        return view('admin.crud.attribute-form', ['attribute' => $attribute->load('values')]);
    }

    public function attributeUpdate(Request $request, Attribute $attribute)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:attributes,name,'.$attribute->id]]);
        $this->attributes->update($attribute, $data['name'], $request->user());
        return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Attribute updated.');
    }

    public function attributeDelete(Request $request, Attribute $attribute)
    {
        if ($attribute->values()->whereHas('variants')->exists()) {
            return back()->withErrors(['attribute' => 'This attribute is used by a product variant. Remove those variants first.']);
        }

        $this->attributes->delete($attribute, $request->user());
        return redirect()->route('admin.attributes')->with('success', 'Attribute deleted.');
    }

    public function attributeValueStore(Request $request, Attribute $attribute)
    {
        $data = $request->validate(['value' => ['required', 'string', 'max:100', 'unique:attribute_values,value,NULL,id,attribute_id,'.$attribute->id]]);
        $this->attributes->addValue($attribute, $data['value'], $request->user());
        return back()->with('success', 'Attribute value added.');
    }

    public function attributeValueDelete(Request $request, AttributeValue $value)
    {
        $attribute = $value->attribute;
        try {
            $this->attributes->deleteValue($value, $request->user());
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            return back()->withErrors(['value' => $exception->getMessage()]);
        }

        return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Attribute value deleted.');
    }

    public function categoryCreate(Request $request)
    {
        $level = $this->requestedCategoryLevel($request);

        return view('admin.crud.form', [
            'type' => 'category',
            'record' => new Category,
            'parents' => $this->categoryParentsForLevel($level),
            'categoryLevel' => $level,
        ]);
    }

    public function categoryStore(Request $request)
    {
        $level = $this->requestedCategoryLevel($request);
        $this->categories->create($this->categoryData($request, $level), $request->user());

        return redirect()->route($this->categoryIndexRoute($level))->with('success', Str::headline($level).' created.');
    }

    public function categoryEdit(Category $category)
    {
        $level = $this->categoryLevel($category);

        return view('admin.crud.form', [
            'type' => 'category',
            'record' => $category,
            'parents' => $this->categoryParentsForLevel($level, $category),
            'categoryLevel' => $level,
        ]);
    }

    public function categoryUpdate(Request $request, Category $category)
    {
        $level = $this->categoryLevel($category);
        $oldIconPath = $category->icon_path;
        $data = $this->categoryData($request, $level);
        $this->categories->update($category, $data, $request->user());

        if (array_key_exists('icon_path', $data) && $oldIconPath && $oldIconPath !== $data['icon_path']) {
            Storage::disk('media')->delete($oldIconPath);
        }

        return redirect()->route($this->categoryIndexRoute($level))->with('success', Str::headline($level).' updated.');
    }

    public function categoryDelete(Request $request, Category $category)
    {
        $this->categories->delete($category, $request->user());
        return back()->with('success', 'Category deleted.');
    }

    public function brandCreate()
    {
        return view('admin.crud.form', ['type' => 'brand', 'record' => new Brand]);
    }

    public function brandStore(Request $request)
    {
        $this->brands->create($this->brandData($request), $request->user());
        return redirect()->route('admin.brands')->with('success', 'Brand created.');
    }

    public function brandEdit(Brand $brand)
    {
        return view('admin.crud.form', ['type' => 'brand', 'record' => $brand]);
    }

    public function brandUpdate(Request $request, Brand $brand)
    {
        $this->brands->update($brand, $this->brandData($request), $request->user());
        return redirect()->route('admin.brands')->with('success', 'Brand updated.');
    }

    public function brandDelete(Request $request, Brand $brand)
    {
        $this->brands->delete($brand, $request->user());
        return back()->with('success', 'Brand deleted.');
    }

    public function couponCreate()
    {
        return view('admin.crud.form', ['type' => 'coupon', 'record' => new Coupon]);
    }

    public function couponStore(Request $request)
    {
        $this->coupons->create($this->couponData($request), $request->user());
        return redirect()->route('admin.coupons')->with('success', 'Coupon created.');
    }

    public function couponEdit(Coupon $coupon)
    {
        return view('admin.crud.form', ['type' => 'coupon', 'record' => $coupon]);
    }

    public function couponUpdate(Request $request, Coupon $coupon)
    {
        $this->coupons->update($coupon, $this->couponData($request, $coupon), $request->user());
        return redirect()->route('admin.coupons')->with('success', 'Coupon updated.');
    }

    public function couponDelete(Request $request, Coupon $coupon)
    {
        $this->coupons->delete($coupon, $request->user());
        return back()->with('success', 'Coupon deleted.');
    }

    private function categoryData(Request $request, string $level): array
    {
        $data = $request->validate(['parent_id' => ['nullable', 'exists:categories,id'], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'sort_order' => ['nullable', 'integer'], 'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'remove_icon' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'is_featured' => ['nullable', 'boolean']]);

        if ($level === 'category') {
            $data['parent_id'] = null;
        } elseif (empty($data['parent_id']) || ! $this->categoryParentsForLevel($level)->contains('id', (int) $data['parent_id'])) {
            abort(422, 'Select a valid parent for this category level.');
        }

        unset($data['icon'], $data['remove_icon']);

        if ($request->hasFile('icon')) {
            $data['icon_path'] = $request->file('icon')->store('categories/icons', 'media');
        } elseif ($request->boolean('remove_icon')) {
            $data['icon_path'] = null;
        }

        return $data + ['is_active' => $request->boolean('is_active'), 'is_featured' => $request->boolean('is_featured')];
    }

    private function requestedCategoryLevel(Request $request): string
    {
        $level = $request->input('level', $request->query('level'));

        return in_array($level, ['subcategory', 'sub-subcategory'], true)
            ? $level
            : 'category';
    }

    private function categoryLevel(Category $category): string
    {
        if ($category->parent_id === null) {
            return 'category';
        }

        return $category->parent()->value('parent_id') === null ? 'subcategory' : 'sub-subcategory';
    }

    private function categoryParentsForLevel(string $level, ?Category $except = null)
    {
        return Category::query()
            ->when($level === 'subcategory', fn ($query) => $query->whereNull('parent_id'))
            ->when($level === 'sub-subcategory', fn ($query) => $query->whereHas('parent', fn ($parent) => $parent->whereNull('parent_id')))
            ->when($level === 'category', fn ($query) => $query->whereRaw('1 = 0'))
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->orderBy('name')
            ->get();
    }

    private function categoryIndexRoute(string $level): string
    {
        return match ($level) {
            'subcategory' => 'admin.categories.subcategories',
            'sub-subcategory' => 'admin.categories.sub-subcategories',
            default => 'admin.categories',
        };
    }

    private function brandData(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'website' => ['nullable', 'url', 'max:255'], 'is_active' => ['nullable', 'boolean']]) + ['is_active' => $request->boolean('is_active')];
    }

    private function couponData(Request $request, ?Coupon $coupon = null): array
    {
        return $request->validate(['code' => ['required', 'string', 'max:50', 'unique:coupons,code'.($coupon ? ','.$coupon->id : '')], 'type' => ['required', 'in:percentage,fixed,free_shipping'], 'value' => ['required_if:type,percentage,fixed', 'nullable', 'numeric', 'min:0'], 'min_order_amount' => ['nullable', 'numeric', 'min:0'], 'usage_limit' => ['nullable', 'integer', 'min:1'], 'per_user_limit' => ['nullable', 'integer', 'min:1'], 'starts_at' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date'], 'description' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean']]) + ['is_active' => $request->boolean('is_active')];
    }
}
