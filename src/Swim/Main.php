<?php

declare(strict_types=1);

namespace Swim;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\PluginBase;

use Swim\packet\SwimmingPacket;

class Main extends PluginBase{

	private static $instance;

	public function onEnable(): void{
		self::$instance = $this;
		PacketPool::registerPacket(new SwimmingPacket());
	}

	public static function get(): self{
		return self::$instance;
	}

	public function isSwimming(Player $player): bool{
		return $player->getGenericFlag(Player::DATA_FLAG_SWIMMING);
	}

	public function setSwimming(Player $player, bool $value = true): void{
		$player->setGenericFlag(Player::DATA_FLAG_SWIMMING, $value);
	}
}