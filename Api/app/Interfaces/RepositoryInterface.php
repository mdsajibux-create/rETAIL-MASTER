<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface RepositoryInterface
{


    /**
     * @param int|string $limit
     * @param int $page
     * @param string $language
     * @param string $search
     * @param string $sortField
     * @param string $sort
     * @param array $filters
     * @return mixed
     */
    public function getPaginatedList(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters );

    /**
     * @return mixed
     */
    public function translationKeys();

    /**
     * @return mixed
     */
    public function index();

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id);

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data);

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id);

    /**
     * @param int|string $id
     * @param string $status
     * @return mixed
     */
    public function changeStatus(int|string $id, string $status="");

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);
}
