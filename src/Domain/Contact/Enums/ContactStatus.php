<?php

declare(strict_types=1);

namespace Domain\Contact\Enums;

enum ContactStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Active = 'active';
    case Failed = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendente',
            self::Processing => 'Processando',
            self::Active => 'Ativo',
            self::Failed => 'Falhou',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::Pending => in_array($newStatus, [self::Processing]),
            self::Processing => in_array($newStatus, [self::Active, self::Failed]),
            self::Active => false,
            self::Failed => in_array($newStatus, [self::Processing]),
        };
    }
}
