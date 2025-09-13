<?php

namespace App\OpenApi;
//Classe uniquement pour la doc
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model\SecurityScheme;

class JwtDecorator implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        //Récupération composants
        $components = $openApi->getComponents();

        // Ajout du security scheme sans écraser les existants
        $securitySchemes = $components->getSecuritySchemes() ?? [];
        $securitySchemes['bearerAuth'] = new SecurityScheme(
            'http',        // type
            '',          // description
            'bearer',      // scheme
            null,          // bearerFormat (deprecated in some versions)
            'JWT'          // Custom: utilisé comme bearerFormat si supporté
        );

        // Mise à jour composants
        $components = $components->withSecuritySchemes($securitySchemes);

        return $openApi
            ->withComponents($components)
            ->withSecurity([['bearerAuth' => []]]);
    }
}
