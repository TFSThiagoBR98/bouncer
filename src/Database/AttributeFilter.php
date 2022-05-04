<?php

namespace Silber\Bouncer\Database;

trait AttributePermission
{
    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden()
    {
        $policy = Gate::getPolicyFor(static::class);

        // If no policy found, check does this extend another model
        // and try get the policy from that one
        if (!$policy && static::class !== self::class) {
            $policy = Gate::getPolicyFor(self::class);
        }

        if (!$policy) {
            return $this->hidden;
        }

        return array_values(array_filter($this->hidden, function ($attribute) use ($policy) {
            $ability = $this->getAttributeViewAbilityMethod($attribute);

            if (is_callable([$policy, $ability])) {
                return Gate::denies($ability, $this);
            }

            return true;
        }));
    }

    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        $policy = Gate::getPolicyFor(static::class);

        // If no policy found, check does this extend another model
        // and try get the policy from that one
        if (!$policy && static::class !== self::class) {
            $policy = Gate::getPolicyFor(self::class);
        }

        if (!$policy) {
            return $this->fillable;
        }

        return array_values(array_filter($this->fillable, function ($attribute) use ($policy) {
            $ability = $this->getAttributeUpdateAbilityMethod($attribute);

            if (is_callable([$policy, $ability])) {
                Gate::allows($ability, $this);
            }

            return true;
        }));
    }

    /**
     * Get the method name for the attribute visibility ability in the model policy.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeViewAbilityMethod($attribute)
    {
        return 'see.' . Str::studly($attribute);
    }

    /**
     * Get the model policy ability method name to update an model attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeUpdateAbilityMethod($attribute)
    {
        return 'fill.' . Str::studly($attribute);
    }
}
