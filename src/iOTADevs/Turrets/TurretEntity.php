<?php

/**
 *
 * d8b  .d88888b. 88888888888     d8888 8888888b.
 * Y8P d88P" "Y88b    888        d88888 888  "Y88b
 *     888     888    888       d88P888 888    888
 * 888 888     888    888      d88P 888 888    888  .d88b.  888  888 .d8888b
 * 888 888     888    888     d88P  888 888    888 d8P  Y8b 888  888 88K
 * 888 888     888    888    d88P   888 888    888 88888888 Y88  88P "Y8888b.
 * 888 Y88b. .d88P    888   d8888888888 888  .d88P Y8b.      Y8bd8P       X88
 * 888  "Y88888P"     888  d88P     888 8888888P"   "Y8888    Y88P    88888P'
 *
 * This Program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with TeaSpoon.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author iOTADevs
 * @link http://iotadevs.github.io
 *
 *
 * Most of the code of this Turret plugin is by @CortexPE :joy:
 */

declare(strict_types = 1);

namespace iOTADevs\Turrets;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\level\Level;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\Server;

class TurretEntity extends Human {

	const NETWORK_ID = -1;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->setDataProperty(self::DATA_SCALE, self::DATA_TYPE_FLOAT, 0.5);
	}

	public function getName(): string{
		return "Turret";
	}

	public function entityBaseTick(int $tickDiff = 1): bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$nDist = Main::$lookRadius; // starting radius
		$nEnti = null;
		$nEntCount = 0;
		foreach($this->getLevel()->getNearbyEntities($this->getBoundingBox()->grow($nDist, $nDist, $nDist), $this) as $e){
			if(!($e instanceof TurretEntity) && $e instanceof Living && $e->isAlive()){
				if($e->distance($this->asVector3()) < $nDist){
					$nDist = $e->distance($this->asVector3());
					$nEnti = $e;
				}
				$nEntCount++;
			}
		}


		$currentTick = Server::getInstance()->getTick();
		$hasTarget = false;

		if($nEnti !== null){
			/*
			$pk = new PlaySoundPacket();
			$pk->soundName = "mob.wither.idle";
			$pk->x = $this->getX();
			$pk->y = $this->getY();
			$pk->z = $this->getZ();
			$pk->pitch = 1;
			$pk->volume = 500;
			Server::getInstance()->broadcastPacket($this->getViewers(), $pk);

			TODO: USE "Target Acquired" AND "Target Lost" SOUNDS

			$pk = new PlaySoundPacket();
			$pk->soundName = "mob.wither.death";
			$pk->x = $this->getX();
			$pk->y = $this->getY();
			$pk->z = $this->getZ();
			$pk->pitch = 1;
			$pk->volume = 500;
			Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
			*/

			if(!($nEnti instanceof Player && $nEnti->getGamemode() == Player::SPECTATOR || in_array($nEnti->getName(), json_decode($this->namedtag->friends->getValue())))){
				$this->lookAtEntity($nEnti);
			}
			if($nDist <= Main::$shootRadius){
				if($currentTick % Main::$shootPerTick == 0){
					if($nEnti instanceof Player){
						if($nEnti->getGamemode() != Player::CREATIVE && $nEnti->getGamemode() != Player::SPECTATOR && !in_array($nEnti->getName(), json_decode($this->namedtag->friends->getValue()))){
							$this->shootAtDirection($this->getDirectionVector());
							$hasTarget = true;
						}
					}else{
						$this->shootAtDirection($this->getDirectionVector());
						$hasTarget = true;
					}
				}
				if($currentTick % (Main::$shootPerTick * 3) == 0 && $hasTarget){
					$pk = new PlaySoundPacket();
					$pk->soundName = "mob.wither.shoot";
					$pk->x = $this->getX();
					$pk->y = $this->getY();
					$pk->z = $this->getZ();
					$pk->pitch = 1;
					$pk->volume = 500;
					Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
				}
			}
		}

		return $hasUpdate;
	}

	private function shootAtDirection(Vector3 $direction){
		$nbt = Entity::createBaseNBT(
			$this->add(0, 1, 0),
			$direction,
			$this->yaw,
			$this->pitch
		);

		$force = min(((10 ** 2) + 10 * 2) / 3, 1) * 2;

		/** @var LaserEntity $laser */
		$laser = Entity::createEntity("Laser", $this->getLevel(), $nbt, $this, $force == 2);
		$laser->setMotion($laser->getMotion()->multiply(2));
		$laser->setCritical(false);
		$laser->spawnToAll();
	}

	private function lookAtEntity(Entity $entity){
		$xdiff = $entity->x - $this->x;
		$zdiff = $entity->z - $this->z;
		$angle = atan2($zdiff, $xdiff);
		$yaw = (($angle * 180) / M_PI) - 90;
		$ydiff = $entity->y - $this->y;
		$v = new Vector2($this->x, $this->z);
		$dist = $v->distance($entity->x, $entity->z);
		$angle = atan2($dist, $ydiff);
		$pitch = (($angle * 180) / M_PI) - 90;

		$pk = new MovePlayerPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->asVector3()->add(0, $this->getEyeHeight(), 0);
		$pk->yaw = $yaw;
		$pk->pitch = $pitch;
		$pk->headYaw = $yaw;
		$pk->onGround = $this->onGround;

		$this->yaw = $yaw;
		$this->pitch = $pitch;

		Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
	}
}