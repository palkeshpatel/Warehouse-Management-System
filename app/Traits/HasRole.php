<?php

namespace App\Traits;

trait HasRole
{
    /**
     * Check if user has a specific role by role name
     * 
     * @param string|array $roleName Role name(s) to check
     * @return bool
     */
    public function hasRole($roleName): bool
    {
        if (!$this->role) {
            return false;
        }

        // Support checking multiple roles
        if (is_array($roleName)) {
            return in_array($this->role->name, $roleName);
        }

        return $this->role->name === $roleName;
    }

    /**
     * Check if user is super admin
     * Super admin must have 'super-admin' role and no warehouse assigned
     * 
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin') && $this->warehouse_id === null;
    }

    /**
     * Check if user is admin
     * Admin must have 'admin' role and be assigned to a warehouse
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') && $this->warehouse_id !== null;
    }

    /**
     * Check if user is employee
     * Employee must have 'employee' role and be assigned to a warehouse
     * 
     * @return bool
     */
    public function isEmployee(): bool
    {
        return $this->hasRole('employee') && $this->warehouse_id !== null;
    }
}

