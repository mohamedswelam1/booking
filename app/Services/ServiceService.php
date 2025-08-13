<?php

namespace App\Services;

use App\Contracts\ServiceContract;
use App\Contracts\Repositories\ServiceRepositoryContract;
use App\Models\Service;
use App\Models\User;
use App\Traits\HasDynamicPagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ServiceService implements ServiceContract
{
    use HasDynamicPagination;

    public function __construct(private readonly ServiceRepositoryContract $services)
    {
    }

    public function providerIndex(User $provider, ?Request $request = null): LengthAwarePaginator
    {
        $perPage = $request ? $this->getPerPage($request) : $this->getDefaultPerPage();
        return $this->services->providerServicesQuery($provider)->paginate($perPage);
    }

    public function createForProvider(User $provider, array $attributes): Service
    {
        return $this->services->createForProvider($provider, $attributes);
    }

    public function update(Service $service, array $attributes): Service
    {
        return $this->services->update($service, $attributes);
    }

    public function delete(Service $service): void
    {
        $this->services->delete($service);
    }

    public function listPublished(array $filters = [], ?Request $request = null): LengthAwarePaginator
    {
        $perPage = $request ? $this->getPerPage($request) : $this->getDefaultPerPage();
        
        $query = QueryBuilder::for(Service::class)
            ->allowedFilters([
                AllowedFilter::exact('category_id'),
            ])
            ->where('is_published', true);

        return $query->paginate($perPage);
    }

    public function findById(string $id): Service
    {
        return Service::findOrFail($id);
    }
}


