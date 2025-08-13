<?php

namespace App\Contracts;

use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ServiceContract
{
    public function providerIndex(User $provider, ?Request $request = null): LengthAwarePaginator;
    public function createForProvider(User $provider, array $attributes): Service;
    public function update(Service $service, array $attributes): Service;
    public function delete(Service $service): void;

    public function listPublished(array $filters = [], ?Request $request = null): LengthAwarePaginator;
    public function findById(string $id): Service;
}


