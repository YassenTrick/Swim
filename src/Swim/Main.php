<?php

declare(strict_types=1);

namespace Swim;

use pocketmine\block\StillWater;
use pocketmine\block\Water;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

	public const PREFIX = TextFormat::GREEN . "CLASwim " . TextFormat::RESET;
	public const STOP_ACTION = 0;
	public const START_ACTION = 1;

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @param PlayerMoveEvent $event
	 * @return void
	 */
	public function onMove(PlayerMoveEvent $event) : void{
		$player = $event->getPlayer();
		if($this->inWater($player)){
			if($player->isSprinting()){
				$this->send($player, self::START_ACTION);
				$player->exhaust(0.015, PlayerExhaustEvent::CAUSE_SWIMMING);
				$player->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 10, 0, false));
			}else{
				$this->send($player, self::START_ACTION);
				$player->exhaust(0.015, PlayerExhaustEvent::CAUSE_SWIMMING);
				$player->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 10, 0, false));
			}
		}else{
			$this->send($player, self::STOP_ACTION);
			$player->removeEffect(Effect::NIGHT_VISION);
		}
	}

	/**
	 * @param Player $player
	 * @param int    $action
	 * @return void
	 */
	public function send(Player $player, int $action) : void{
		switch($action){
			case self::STOP_ACTION:
				$pk = new PlayerActionPacket();
				$pk->entityRuntimeId = Entity::$entityCount++;
				$pk->x = $player->getFloorX();
				$pk->y = $player->getFloorY();
				$pk->z = $player->getFloorZ();
				$pk->face = $player->getDirection();
				$pk->action = $pk::ACTION_STOP_SWIMMING;
				$player->setGenericFlag($player::DATA_FLAG_SWIMMING, false);
				break;
			case self::START_ACTION:
				$pk = new PlayerActionPacket();
				$pk->entityRuntimeId = Entity::$entityCount++;
				$pk->x = $player->getFloorX();
				$pk->y = $player->getFloorY();
				$pk->z = $player->getFloorZ();
				$pk->face = $player->getDirection();
				$pk->action = $pk::ACTION_START_SWIMMING;
				$player->setGenericFlag($player::DATA_FLAG_SWIMMING, true);
				break;
		}
		$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $pk);
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function inWater(Player $player) : bool{
		return $player->getLevel()->getBlockAt($player->getFloorX(), $player->getFloorY() - 1, $player->getFloorZ()) instanceof Water || $player->getLevel()->getBlockAt($player->getFloorX(), $player->getFloorY() - 1, $player->getFloorZ()) instanceof StillWater;
	}
}