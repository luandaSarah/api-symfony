<?php 

namespace App\Dto\Filter;


class ArticleFilterDto
{
    public function __construct(
    private readonly int $page = 1,
    private readonly int $limit = 6,
    ){
        
    }

    /**
     * Get the value of page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get the value of limit
     */
    public function getLimit()
    {
        return $this->limit;
    }
}