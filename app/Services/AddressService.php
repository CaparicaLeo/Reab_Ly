<?php

namespace App\Services;

use App\Models\Address;

class AddressService
{
    public function __construct(
        private CEPService $cepService
    ) {}

    public function create(array $data): Address
    {
        // Busca os dados do CEP e mescla com os dados do request
        $cepData = $this->cepService->findAddressByCEP($data['postal_code']);

        return Address::create([
            'user_id'      => $data['user_id'],
            'postal_code'          => $data['postal_code'],
            'number'       => $data['number'],
            'complement'   => $data['complement'] ?? null,
            'street'       => $data['street'] ?? $cepData['street'],
            'neighborhood' => $data['neighborhood'] ?? $cepData['neighborhood'],
            'city'         => $data['city'] ?? $cepData['city'],
            'state'        => $data['state'] ?? $cepData['state'],
        ]);
    }
}
