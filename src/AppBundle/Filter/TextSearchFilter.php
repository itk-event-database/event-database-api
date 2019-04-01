<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TextSearchFilter extends AbstractFilter
{
    private $name = 'search';

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, LoggerInterface $logger = null, array $properties = null)
    {
        $properties += [
            'name' => 'search',
        ];
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);

        $this->name = $this->properties['name'];
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            sprintf('%s[%s]', $this->name, 'fields') => [
                'property' => $this->name,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Fields to search. Comma-separated list or array.',
                    'type' => 'string',
                ],
            ],
            sprintf('%s[%s]', $this->name, 'terms') => [
                'property' => $this->name,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Terms to search for. Non-array will be split by white-space and trimmed.',
                    'type' => 'number',
                ],
            ],
        ];
    }

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null// , array $context = []
    ) {
        if ($property !== $this->name || !is_array($value) || !isset($value['terms'])) {
            return;
        }

        // Get terms.
        $terms = $value['terms'];
        if (!is_array($terms)) {
            $terms = preg_split('/\s/', $terms);
        }
        $terms = array_map('trim', $terms);

        // Get field names.
        $fields = $value['fields'] ?? $this->properties['fields']['default'] ?? null;
        if (empty($fields)) {
            return;
        }
        if (!is_array($fields)) {
            $fields = preg_split('/,/', $fields);
        }
        $fields = array_map('trim', $fields);

        // Validate that fields exist in entity.
        $metadata = $this->getClassMetadata($resourceClass);
        $fields = array_values(array_intersect($fields, $metadata->fieldNames));

        if (empty($fields)) {
            return;
        }

        // Apply conditions.
        $alias = 'o';
        $termCondition = new Andx();
        foreach ($terms as $termIndex => $term) {
            $parameterName = sprintf('%s_term_%d', $this->name, $termIndex);
            $fieldCondition = new Orx();
            foreach ($fields as $field) {
                $fieldCondition->add(sprintf(
                    '%s.%s LIKE :%s',
                    $alias,
                    $field,
                    $parameterName
                ));
            }
            $queryBuilder->setParameter($parameterName, $this->makeLikeParam($term));
            $termCondition->add($fieldCondition);
        }
        $queryBuilder->andWhere($termCondition);
    }

    // @see https://gist.github.com/johnkary/9770413

    /**
     * Format a value that can be used as a parameter for a DQL LIKE search.
     *
     * $qb->where("u.name LIKE (:name) ESCAPE '!'")
     *    ->setParameter('name', $this->makeLikeParam('john'))
     *
     * NOTE: You MUST manually specify the `ESCAPE '!'` in your DQL query, AND the
     * ! character MUST be wrapped in single quotes, else the Doctrine DQL
     * parser will throw an error:
     *
     * [Syntax Error] line 0, col 127: Error: Expected Doctrine\ORM\Query\Lexer::T_STRING, got '"'
     *
     * Using the $pattern argument you can change the LIKE pattern your query
     * matches again. Default is "%search%". Remember that "%%" in a sprintf
     * pattern is an escaped "%".
     *
     * Common usage:
     *
     * ->makeLikeParam('foo')         == "%foo%"
     * ->makeLikeParam('foo', '%s%%') == "foo%"
     * ->makeLikeParam('foo', '%s_')  == "foo_"
     * ->makeLikeParam('foo', '%%%s') == "%foo"
     * ->makeLikeParam('foo', '_%s')  == "_foo"
     *
     * Escapes LIKE wildcards using '!' character:
     *
     * ->makeLikeParam('foo_bar') == "%foo!_bar%"
     *
     * @param string $search  Text to search for LIKE
     * @param string $pattern sprintf-compatible substitution pattern
     *
     * @return string
     */
    protected function makeLikeParam($search, $pattern = '%%%s%%')
    {
        /**
         * Function defined in-line so it doesn't show up for type-hinting on
         * classes that implement this trait.
         *
         * Makes a string safe for use in an SQL LIKE search query by escaping all
         * special characters with special meaning when used in a LIKE query.
         *
         * Uses ! character as default escape character because \ character in
         * Doctrine/DQL had trouble accepting it as a single \ and instead kept
         * trying to escape it as "\\". Resulted in DQL parse errors about "Escape
         * character must be 1 character"
         *
         * % = match 0 or more characters
         * _ = match 1 character
         *
         * Examples:
         *      gloves_pink   becomes  gloves!_pink
         *      gloves%pink   becomes  gloves!%pink
         *      glo_ves%pink  becomes  glo!_ves!%pink
         *
         * @param string $search
         *
         * @return string
         */
        $sanitizeLikeValue = function ($search) {
            $escapeChar = '!';
            $escape = [
                '\\'.$escapeChar, // Must escape the escape-character for regex
                '\%',
                '\_',
            ];
            $pattern = sprintf('/([%s])/', implode('', $escape));

            return preg_replace($pattern, $escapeChar.'$0', $search);
        };

        return sprintf($pattern, $sanitizeLikeValue($search));
    }
}
