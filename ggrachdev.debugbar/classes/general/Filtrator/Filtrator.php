<?php

namespace GGrach\Filtrator;

use \GGrach\Filtrator\FiltratorContract;

/**
 * @author ggrachdev
 */
class Filtrator implements FiltratorContract {

    public const FILTERS_NAME = [
        'limit', 'first', 'last', 'keys'
    ];

    private $customFilters = [];

    /**
     * 
     * @var array
     */
    protected $sequenceFilters;

    public function addFilter(string $filterType, array $filterParams = []): void {
        if ($this->hasFilter($filterType) || $this->hasCustomFilter($filterType)) {
            $this->sequenceFilters[] = [
                'type' => $filterType,
                'params' => $filterParams
            ];
        }
    }

    public function filtrateItem(string $filterType, array $filterParams, $data) {
        switch ($filterType) {
            case 'limit':
                if (\is_array($data) && !empty($data)) {
                    if (empty($filterParams['count']) || $filterParams['count'] < 1) {
                        $filterParams['count'] = 10;
                    }
                    $data = array_slice($data, 0, $filterParams['count'], true);
                }
                break;
            case 'first':
                if (\is_array($data) && !empty($data)) {
                    $data = array_shift($data);
                }
                break;
            case 'keys':
                if (\is_array($data) && !empty($data) && !empty($filterParams['keys'])) {
                    $newData = [];

                    foreach ($data as $k => $v) {
                        if (\in_array($k, $filterParams['keys'])) {
                            $newData[$k] = $v;
                        }
                    }

                    $data = $newData;
                }
                break;
            case 'last':
                if (\is_array($data) && !empty($data)) {
                    $data = array_pop($data);
                }
                break;
        }

        return $data;
    }

    public function clearFilters(): void {
        $this->sequenceFilters = [];
    }

    public function filtrate($data) {
        if (!empty($this->sequenceFilters) && !empty($data)) {
            foreach ($this->sequenceFilters as $arFilter) {
                if($this->hasCustomFilter($arFilter['type']))
                {
                    $data = $this->customFiltrateItem($arFilter['type'], $arFilter['params'], $data);
                }
                else {
                    $data = $this->filtrateItem($arFilter['type'], $arFilter['params'], $data);
                }
            }
        }

        return $data;
    }

    public function hasFilter(string $filterType): bool {
        return \in_array($filterType, self::FILTERS_NAME);
    }

    public function addCustomFilter(string $filterName, callable $callback) {
        if (!$this->hasCustomFilter($filterName)) {
            $this->customFilters[$filterName] = $callback;
        }

        return $this;
    }

    public function customFiltrateItem(string $filterType, array $filterParams, $data) {
        return $this->customFilters[$filterType]($data, $filterParams);
    }

    public function hasCustomFilter(string $filterName) {
        return \array_key_exists($filterName, $this->customFilters);
    }

}
