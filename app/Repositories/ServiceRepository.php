<?php

namespace App\Repositories;

use App\Contracts\Repositories\ServiceRepositoryContract;
use App\Models\Service;
use App\Models\User;

class ServiceRepository implements ServiceRepositoryContract
{
    public function createForProvider(User $provider, array $attributes): Service
    {
        $attributes['provider_id'] = $provider->id;
        return Service::create($attributes);
    }

    public function update(Service $service, array $attributes): Service
    {
        $service->update($attributes);
        return $service->refresh();
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }

    public function providerServicesQuery(User $provider)
    {
        return Service::query()->where('provider_id', $provider->id);
    }
}


