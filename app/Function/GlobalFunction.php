<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;



function paginateIndexModule(Builder $moduleBuilder): PaginationLengthAwarePaginator
{
    return $moduleBuilder->paginate(request('search_per_page', 10));
}
