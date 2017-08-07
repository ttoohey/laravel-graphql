<?php

namespace Folklore\GraphQL\Relay;

use Illuminate\Database\Eloquent\Collection;

class EdgesCollection extends Collection
{
    protected $startCursor = null;
    protected $endCursor = null;
    protected $hasNextPage = false;
    protected $hasPreviousPage = false;
    protected $totalCount = 0;

    public function setHasNextPage($hasNextPage)
    {
        $this->hasNextPage = $hasNextPage;
    }

    public function getHasNextPage()
    {
        return $this->hasNextPage;
    }

    public function setHasPreviousPage($hasPreviousPage)
    {
        $this->hasPreviousPage = $hasPreviousPage;
    }

    public function getHasPreviousPage()
    {
        return $this->hasPreviousPage;
    }

    public function setStartCursor($startCursor)
    {
        $this->startCursor = $startCursor;
    }

    public function getStartCursor()
    {
        return $this->startCursor;
    }

    public function setEndCursor($endCursor)
    {
        $this->endCursor = $endCursor;
    }

    public function getEndCursor()
    {
        return $this->endCursor;
    }
    
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }
    
    public function getTotalCount()
    {
        return $this->totalCount;
    }
}
