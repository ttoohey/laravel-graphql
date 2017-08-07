<?php

namespace Folklore\GraphQL\Relay\Support;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InterfaceType;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

use Folklore\GraphQL\Relay\EdgesCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ConnectionType extends BaseType
{
    protected $edgeType;

    protected function edgeType()
    {
        return null;
    }

    protected function fields()
    {
        return [
            'edges' => [
                'type' => Type::listOf($this->getEdgeObjectType()),
                'resolve' => function ($root) {
                    return $this->getEdgesFromRoot($root);
                }
            ],
            'pageInfo' => [
                'type' => GraphQL::type('PageInfo'),
                'resolve' => function ($root) {
                    return $this->getPageInfoFromRoot($root);
                }
            ],
            'totalCount' => [
                'type' => Type::Int(),
                'resolve' => function ($root) {
                    return $this->getTotalCountFromRoot($root);
                }
            ]
        ];
    }

    public function getEdgeType()
    {
        $edgeType = $this->edgeType();
        return $edgeType ? $edgeType:$this->edgeType;
    }

    public function setEdgeType($edgeType)
    {
        $this->edgeType = $edgeType;
        return $this;
    }

    protected function getEdgeObjectType()
    {
        $edgeType = $this->getEdgeType();
        $name = $edgeType->config['name'].'Edge';
        GraphQL::addType(\Folklore\GraphQL\Relay\ConnectionEdgeType::class, $name);
        $type = GraphQL::type($name);
        $type->setEdgeType($edgeType);
        return $type;
    }

    protected function getCursorFromNode($edge)
    {
        $edgeType = $this->getEdgeType();
        if ($edgeType instanceof InterfaceType) {
            $edgeType = $edgeType->config['resolveType']($edge);
        }
        $resolveId = $edgeType->getField('id')->resolveFn;
        return $resolveId($edge);
    }

    protected function getEdgesFromRoot($root)
    {
        $cursor = $this->getStartCursorFromRoot($root);
        $edges = [];
        foreach ($root as $item) {
            $edges[] = [
                'cursor' => $cursor !== null ? $cursor:$this->getCursorFromNode($item),
                'node' => $item
            ];
            if ($cursor !== null) {
                $cursor++;
            }
        }
        return $edges;
    }

    protected function getHasPreviousPageFromRoot($root)
    {
        $hasPreviousPage = false;
        if ($root instanceof LengthAwarePaginator) {
            $hasPreviousPage = !$root->onFirstPage();
        } elseif ($root instanceof AbstractPaginator) {
            $hasPreviousPage = !$root->onFirstPage();
        } elseif ($root instanceof EdgesCollection) {
            $hasPreviousPage = $root->getHasPreviousPage();
        }

        return $hasPreviousPage;
    }

    protected function getHasNextPageFromRoot($root)
    {
        $hasNextPage = false;
        if ($root instanceof LengthAwarePaginator) {
            $hasNextPage = $root->hasMorePages();
        } elseif ($root instanceof EdgesCollection) {
            $hasNextPage = $root->getHasNextPage();
        }

        return $hasNextPage;
    }

    protected function getStartCursorFromRoot($root)
    {
        $startCursor = null;
        if ($root instanceof EdgesCollection) {
            $startCursor = $root->getStartCursor();
        }

        return $startCursor;
    }

    protected function getEndCursorFromRoot($root)
    {
        $endCursor = null;
        if ($root instanceof EdgesCollection) {
            $endCursor = $root->getEndCursor();
        }

        return $endCursor;
    }

    protected function getPageInfoFromRoot($root)
    {
        $hasPreviousPage = $this->getHasPreviousPageFromRoot($root);
        $hasNextPage = $this->getHasNextPageFromRoot($root);
        $startCursor = $this->getStartCursorFromRoot($root);
        $endCursor = $this->getEndCursorFromRoot($root);
        $edges = $startCursor === null || $endCursor === null ? $this->getEdgesFromRoot($root):null;

        return [
            'hasPreviousPage' => $hasPreviousPage,
            'hasNextPage' => $hasNextPage,
            'startCursor' => $startCursor !== null ? $startCursor:array_get($edges, '0.cursor'),
            'endCursor' => $endCursor !== null ? $endCursor:array_get($edges, (sizeof($edges)-1).'.cursor')
        ];
    }
    
    protected function getTotalCountFromRoot($root)
    {
        $totalCount = null;
        if ($root instanceof EdgesCollection) {
            $totalCount = $root->getTotalCount();
        }

        return $totalCount;
    }
}
