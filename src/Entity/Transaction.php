<?php

namespace App\Entity;

use App\Enum\TransactionRecurringType;
use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use BcMath\Number;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transaction`')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[SerializedName("Transaction Date")]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::NUMBER, precision: 10, scale: 2)]
    #[SerializedName("Amount")]
    private ?Number $amount = null;
    #[ORM\Column(enumType: TransactionType::class)]

    private ?TransactionType $type = null;

    #[ORM\Column(enumType: TransactionRecurringType::class)]
    #[SerializedName("Recurring")]
    private ?TransactionRecurringType $RecurringType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?Number
    {
        return $this->amount;
    }

    public function setAmount(Number $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): ?TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRecurringType(): ?TransactionRecurringType
    {
        return $this->RecurringType;
    }

    public function setRecurringType(TransactionRecurringType $RecurringType): static
    {
        $this->RecurringType = $RecurringType;

        return $this;
    }
}
