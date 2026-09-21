<?php

namespace App\Enums\Commerce;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case UserCancelled = 'user_cancelled';
    case Rejected = 'rejected';
    case ReconciliationRequired = 'reconciliation_required';

    // New state for Selcom INPROGRESS (different from legacy Processing)
    case InProgress = 'in_progress';

    /**
     * Check if this status is a terminal state (no further transitions allowed)
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Paid,
            self::Cancelled,
            self::UserCancelled,
            self::Rejected,
            self::Failed,
            self::Refunded => true,
            default => false,
        };
    }

    /**
     * Check if this status allows a new payment attempt for the same order
     */
    public function allowsNewPaymentAttempt(): bool
    {
        return match ($this) {
            self::Cancelled,
            self::UserCancelled,
            self::Rejected,
            self::Failed => true,
            default => false,
        };
    }

    /**
     * Check if this status is a pending-like state (can still complete)
     */
    public function isPendingLike(): bool
    {
        return match ($this) {
            self::Pending,
            self::Processing,
            self::InProgress,
            self::ReconciliationRequired => true,
            default => false,
        };
    }

    /**
     * Get all legal transitions from this state
     *
     * @return array<self>
     */
    public function legalTransitions(): array
    {
        return match ($this) {
            self::Pending => [
                self::Processing,
                self::InProgress,
                self::Paid,
                self::Cancelled,
                self::UserCancelled,
                self::Rejected,
                self::ReconciliationRequired,
                self::Failed,
            ],
            self::Processing => [
                self::Paid,
                self::Cancelled,
                self::UserCancelled,
                self::Rejected,
                self::Failed,
            ],
            self::InProgress => [
                self::Paid,
                self::Cancelled,
                self::UserCancelled,
                self::Rejected,
                self::Failed,
            ],
            self::ReconciliationRequired => [
                self::Paid,
                self::Refunded,
                self::Cancelled,
                self::UserCancelled,
                self::Rejected,
            ],
            self::Paid => [],
            self::Cancelled => [],
            self::UserCancelled => [],
            self::Rejected => [],
            self::Failed => [],
            self::Refunded => [],
        };
    }

    /**
     * Check if a transition to the given state is legal
     */
    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->legalTransitions(), true);
    }

    /**
     * Normalize a provider status string to internal PaymentStatus
     */
    public static function fromProviderStatus(string $providerStatus): self
    {
        return match (strtoupper($providerStatus)) {
            'COMPLETED' => self::Paid,
            'CANCELLED' => self::Cancelled,
            'USERCANCELED', 'USERCANCELLED' => self::UserCancelled,
            'REJECTED' => self::Rejected,
            'INPROGRESS' => self::InProgress,
            'PENDING' => self::Pending,
            'PROCESSING' => self::Processing,
            default => self::Pending,
        };
    }
}
