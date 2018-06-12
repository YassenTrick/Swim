<?php

declare(strict_types=1);

namespace Swim\event;

use pocketmine\Player;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;

class PlayerToggleSwimEvent extends PlayerEvent implements Cancellable{

    /** @var bool */
    protected $isSwimming;

    /**
     * @param Player $player
     * @param bool   $isSwimming
     */
    public function __construct(Player $player, bool $isSwimming){
        $this->player = $player;
        $this->isSwimming = $isSwimming;
    }
    
    /**
     * @return bool
     */
    public function isSwimming() : bool{
        return $this->isSwimming;
    }
}