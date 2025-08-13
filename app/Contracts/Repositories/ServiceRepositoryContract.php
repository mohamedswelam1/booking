<?php

namespace App\Contracts\Repositories;

use App\Models\Service;
use App\Models\User;

interface ServiceRepositoryContract
{
    public function createForProvider(User $provider, array $attributes): Service;
    public function update(Service $service, array $attributes): Service;
    public function delete(Service $service): void;
    public function providerServicesQuery(User $provider);
}


