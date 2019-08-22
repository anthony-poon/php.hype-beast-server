<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PullEntry
 * @package App\Entity
 * @ORM\Entity()
 * @ORM\Table()
 */
class PollEntry {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $label;
    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $count;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLabel(): int {
        return $this->label;
    }

    /**
     * @param int $label
     */
    public function setLabel(int $label): void {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getCount(): int {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void {
        $this->count = $count;
    }


}