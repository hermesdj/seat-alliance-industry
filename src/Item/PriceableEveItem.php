<?php

namespace RecursiveTree\Seat\AllianceIndustry\Item;

use RecursiveTree\Seat\TreeLib\Items\EveItem;
use Seat\Services\Contracts\IPriceable;

class PriceableEveItem extends EveItem implements IPriceable
{
    public function getTypeID(): int
    {
        return $this->typeModel->typeID;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setPrice(float $price): void
    {
        // Price provided is the full price with all the items, not the individual price
        $this->price = $price / $this->getAmount();
    }
}