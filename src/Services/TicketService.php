<?php


namespace App\Services;

use App\Repositories\TicketRepository;
use App\Repositories\DeviceRepository;

class TicketService
{
    public function __construct(
        private TicketRepository $tickets,
        private DeviceRepository $devices
    ) {
    }

    public function listForUser(int $userId, string $role): array
    {
        return $this->tickets->findVisibleTo($userId, $role);
    }
    public function availableDevices(int $userId, string $role): array
    {
        if ($role !== 'EMPLOYEE') {
            throw new \LogicException('Tikety môže vytvárať iba zamestnanec.');
        }

        return $this->devices->findAvailableTo($userId);
    }

    public function create(int $userId, string $role, array $data): array
    {
        if ($role !== 'EMPLOYEE') {
            throw new \LogicException('Tikety môže vytvárať iba zamestnanec.');
        }

        $errors = [];

        if ($data['title'] === '' || mb_strlen($data['title']) > 200) {
            $errors[] = 'Názov musí obsahovať 1 až 200 znakov.';
        }

        if ($data['description'] === '') {
            $errors[] = 'Vyplň opis problému.';
        } elseif (strlen($data['description']) > 4000) {
            $errors[] = 'Opis môže mať najviac 4000 bajtov.';
        }

        if (!in_array($data['priority'], ['LOW', 'MEDIUM', 'HIGH'], true)) {
            $errors[] = 'Vyber platnú prioritu.';
        }

        $deviceId = filter_var(
            $data['device_id'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $availableIds = array_map(
            static fn(array $device): int => (int) $device['id'],
            $this->devices->findAvailableTo($userId)
        );

        if ($deviceId === false
            || !in_array($deviceId, $availableIds, true)) {
            $errors[] = 'Vyber svoje alebo spoločné zariadenie.';
        }

        if ($errors !== []) {
            return $errors;
        }

        $this->tickets->create(
            $data['title'],
            $data['description'],
            $userId,
            $deviceId,
            $data['priority']
        );

        return [];
    }

    public function detailForUser(
        int $ticketId,
        int $userId,
        string $role
    ): ?array {
        if (!in_array($role, ['EMPLOYEE', 'TECHNICIAN'], true)) {
            return null;
        }

        $ticket = $this->tickets->findById($ticketId);

        if ($ticket === null) {
            return null;
        }

        if ($role === 'EMPLOYEE'
            && (int) $ticket['author_id'] !== $userId) {
            return null;
        }

        return $ticket;
    }

}