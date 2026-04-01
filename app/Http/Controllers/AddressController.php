<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(
        private AddressService $addressService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();
        return response()->json($addresses);
    }

    public function store(AddressRequest $request): JsonResponse
    {
        try {
            $address = $this->addressService->create($request->validated());

            return response()->json($address, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function show(Address $address): JsonResponse
    {
        return response()->json($address);
    }

    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        $address->update($request->validated());

        return response()->json($address);
    }

    public function destroy(Address $address): JsonResponse
    {
        $address->delete();

        return response()->json(['message' => 'Endereço removido com sucesso.']);
    }
}
