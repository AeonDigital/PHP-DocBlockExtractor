<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor;

use AeonDigital\DocBlockExtractor\DataObject as DataObject;
use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;






/**
 * Informações básicas extraidas da análise dos objetos mapeados
 */
class DocBlockExtractor
{



    /** @var array $namespaces */
    private array $namespaces = [];
    /**
     * Retorna um array unidimensional contendo todas as namespaces
     * declaradas no projeto.
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }



    /** @var array[string]DataObject $constants */
    private array $constants;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e as constantes.
     *
     * @return array[string]DataObject
     */
    public function getConstants(): array
    {
        return $this->constants;
    }



    /** @var array[string]DataObject $variables */
    private array $variables;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e as variáveis.
     *
     * @return array[string]DataObject
     */
    public function getVariables(): array
    {
        return $this->variables;
    }



    /** @var array[string]DataObject $functions */
    private array $functions;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e as funções.
     *
     * @return array[string]DataObject
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }



    /** @var array[string]DataObject $interfaces */
    private array $interfaces;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e suas interfaces.
     *
     * @return array[string]DataObject
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }



    /** @var array[string]DataObject $enums */
    private array $enums;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e seus enuns.
     *
     * @return array[string]DataObject
     */
    public function getEnuns(): array
    {
        return $this->enums;
    }



    /** @var array[string]DataObject $traits */
    private array $traits;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e suas traits.
     *
     * @return array[string]DataObject
     */
    public function getTraits(): array
    {
        return $this->traits;
    }



    /** @var array[string]DataObject $classes */
    private array $classes;
    /**
     * Retorna um array associativo fazendo o vínculo entre as namespaces do projeto
     * e suas classes.
     *
     * @return array[string]DataObject
     */
    public function getClasses(): array
    {
        return $this->classes;
    }




    /**
     * Adiciona uma nova constante na lista.
     *
     * @param DataObject $obj
     * Objeto a ser adicionado.
     *
     * @return void
     */
    public function addObject(DataObject $obj): void
    {
        $namespace = $obj->getNamespace();
        if (\in_array($namespace, $this->namespaces) === false) {
            $this->namespaces[] = $namespace;

            $this->constants[$namespace] = [];
            $this->variables[$namespace] = [];
            $this->functions[$namespace] = [];

            $this->interfaces[$namespace] = [];
            $this->enums[$namespace] = [];
            $this->traits[$namespace] = [];
            $this->classes[$namespace] = [];
        }


        switch ($obj->getType()) {
            case ElementType::CONSTANT:
                $this->constants[$namespace][] = $obj;
                break;

            case ElementType::FUNCTION:
                $this->functions[$namespace][] = $obj;
                break;


            case ElementType::INTERFACE:
                $this->interfaces[$namespace][] = $obj;
                break;

            case ElementType::ENUM:
                $this->enums[$namespace][] = $obj;
                break;

            case ElementType::TRAIT:
                $this->traits[$namespace][] = $obj;
                break;

            case ElementType::CLASSE:
                $this->classes[$namespace][] = $obj;
                break;
        }
    }




    /**
     * Retorna um array associativo representando todos os dados coletados
     * do projeto alvo.
     *
     * @return array
     */
    public function toArray(): array
    {
        $r = [
            "namespaces" => $this->getNamespaces(),
            "constants" => [],
            //"variables" => [],
            //"functions" => [],

            //"interfaces" => [],
            //"enums" => [],
            //"traits" => [],
            //"classes" => [],
        ];


        foreach ($this->getConstants() as $ns => $objs) {
            if (\key_exists($ns, $r["constants"]) === false) {
                $r["constants"][$ns] = [];
            }

            /** @var DataObject $obj */
            foreach ($objs as $obj) {
                $r["constants"][$ns][] = $obj->toArray();
            }
        }



        return $r;
    }
}