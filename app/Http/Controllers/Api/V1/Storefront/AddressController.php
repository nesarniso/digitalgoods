<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Users\Models\Address;
use App\Domain\Users\Services\AddressService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreAddressRequest;
use App\Http\Requests\Storefront\UpdateAddressRequest;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addresses) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Addresses retrieved.',
            'data' => Address::query()->where('user_id', $request->user()->id)->latest('is_default')->get(),
            'meta' => (object) [],
        ]);
    }

    public function store(StoreAddressRequest $request)
    {
        $address = $this->addresses->create($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Address added.',
            'data' => $address,
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404, 'Address not found.');

        $address = $this->addresses->update($address, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Address updated.',
            'data' => $address,
            'meta' => (object) [],
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404, 'Address not found.');

        $this->addresses->delete($address);

        return response()->json([
            'success' => true,
            'message' => 'Address deleted.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}
