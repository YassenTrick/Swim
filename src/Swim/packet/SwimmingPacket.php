<?php

declare(strict_types=1);

namespace Swim\packet;

use pocketmine\{
    Player, Server
};
use pocketmine\block\Water;
use pocketmine\network\mcpe\protocol\{
    ProtocolInfo, PlayerActionPacket
};
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;

use Swim\event\PlayerToggleSwimEvent;
use Swim\Main;

class SwimmingPacket extends PlayerActionPacket{

    public const NETWORK_ID = ProtocolInfo::PLAYER_ACTION_PACKET;

    public const ACTION_START_BREAK = 0;
    public const ACTION_ABORT_BREAK = 1;
    public const ACTION_STOP_BREAK = 2;
    public const ACTION_GET_UPDATED_BLOCK = 3;
    public const ACTION_DROP_ITEM = 4;
    public const ACTION_START_SLEEPING = 5;
    public const ACTION_STOP_SLEEPING = 6;
    public const ACTION_RESPAWN = 7;
    public const ACTION_JUMP = 8;
    public const ACTION_START_SPRINT = 9;
    public const ACTION_STOP_SPRINT = 10;
    public const ACTION_START_SNEAK = 11;
    public const ACTION_STOP_SNEAK = 12;
    public const ACTION_DIMENSION_CHANGE_REQUEST = 13;
    public const ACTION_DIMENSION_CHANGE_ACK = 14;
    public const ACTION_START_GLIDE = 15;
    public const ACTION_STOP_GLIDE = 16;
    public const ACTION_BUILD_DENIED = 17;
    public const ACTION_CONTINUE_BREAK = 18;
    public const ACTION_SET_ENCHANTMENT_SEED = 20;
    public const ACTION_START_SWIMMING = 21;
    public const ACTION_STOP_SWIMMING = 22;
    public const ACTION_START_SPIN_ATTACK = 23;
    public const ACTION_STOP_SPIN_ATTACK = 24;

    /** @var int */
    public $entityRuntimeId;
    /** @var int */
    public $action;
    /** @var int */
    public $x;
    /** @var int */
    public $y;
    /** @var int */
    public $z;
    /** @var int */
    public $face;

    protected function decodePayload(){
        $this->entityRuntimeId = $this->getEntityRuntimeId();
        $this->action = $this->getVarInt();
        $this->getBlockPosition($this->x, $this->y, $this->z);
        $this->face = $this->getVarInt();
        $this->handlePlayerAction($this);
    }

    protected function encodePayload(){
        $this->putEntityRuntimeId($this->entityRuntimeId);
        $this->putVarInt($this->action);
        $this->putBlockPosition($this->x, $this->y, $this->z);
        $this->putVarInt($this->face);
    }

    public function handlePlayerAction(PlayerActionPacket $packet){
        switch($packet->action){
            case self::ACTION_START_SWIMMING:
            foreach(Server::getInstance()->getOnlinePlayers() as $player){
                $pos = new Vector3($packet->x, $packet->y, $packet->z);
                $ev = new PlayerToggleSwimEvent($player, true);
                Server::getInstance()->getPluginManager()->callEvent($ev);
                foreach(Server::getInstance()->getLevels() as $level){
                    $block = $level->getBlock($pos);
                    if(!$block instanceof Water) $ev->setCancelled();
                    if($ev->isCancelled()){
                        $player->sendData($player);
                    }else{
                        $player->getDataPropertyManager()->setFloat(Player::DATA_BOUNDING_BOX_HEIGHT, $player->width);
                        Main::get()->setSwimming($player, true);
                    }
                }
            }
            break;
            case self::ACTION_STOP_SWIMMING:
            foreach(Server::getInstance()->getOnlinePlayers() as $player){
                $ev = new PlayerToggleSwimEvent($player, false);
                Server::getInstance()->getPluginManager()->callEvent($ev);
                if($ev->isCancelled()){
                    $player->sendData($player);
                }else{
                    $player->getDataPropertyManager()->setFloat(Player::DATA_BOUNDING_BOX_HEIGHT, $player->height);
                    Main::get()->setSwimming($player, false);
                }
            }
            break;
        }
    }

    public function handle(NetworkSession $session) : bool{
        return $session->handlePlayerAction($this);
    }
}