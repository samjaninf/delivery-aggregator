<?php

namespace App\Http\GraphQL\Directives;

use GraphQL\Error\Error;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;

class AbilityDirective extends BaseDirective implements FieldMiddleware
{
    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name()
    {
        return 'ability';
    }

    /**
     * Resolve the field directive.
     *
     * @param FieldValue $value
     * @param \Closure    $next
     *
     * @return FieldValue
     */
    public function handleField(FieldValue $value, \Closure $next)
    {
        $abilities = $this->directiveArgValue('if');
        $resolver = $value->getResolver();

        return $next($value->setResolver(
            function ($root, $args) use ($abilities, $resolver) {
                $model = $this->directiveHasArgument('model') ? $this->getModelClass()::find($args['id']) : null;

                $can = collect($abilities)->reduce(function ($allowed, $ability) use ($model) {
                    if (app('auth')->user()->can($ability, $model)) {
                        return $allowed;
                    }

                    return false;
                }, true);

                if (!$can) {
                    throw new Error('Not authorized to access resource');
                }

                return call_user_func_array($resolver, func_get_args());
            }
        ));
    }
}
