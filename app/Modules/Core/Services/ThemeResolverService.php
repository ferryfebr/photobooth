<?php

namespace App\Modules\Core\Services;

class ThemeResolverService
{
    public function resolve(?string $themeSlug): string
    {
        $slug = preg_replace('/[^a-z0-9\-_]/', '', strtolower($themeSlug ?? ''));

        if ($slug) {
            $view = 'invitations.'.$slug.'.index';

            if (view()->exists($view)) {
                return $view;
            }
        }

        return 'invitations.default.index';
    }
}
