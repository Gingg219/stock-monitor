<?php

namespace App\Contracts\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface WarehousesRepository.
 *
 * @package namespace App\Contracts\Repositories;
 */
interface WarehousesRepository extends RepositoryInterface
{
    public function index($filters = []);

    public function store($request);

    public function updateWithRelations($id, array $data);

    public function destroy($id);
}
