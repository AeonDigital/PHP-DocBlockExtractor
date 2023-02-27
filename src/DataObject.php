<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor;

use AeonDigital\DocBlockExtractor\Exceptions\FileNotFoundException as FileNotFoundException;
use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;






/**
 * Informações básicas extraidas da análise dos objetos mapeados
 */
class DataObject
{



    /** @var string $pathToFile */
    private string $pathToFile = "";
    /**
     * Retorna o caminho completo até o arquivo onde este objeto.
     * está declarado.
     */
    public function getPathToFile(): string
    {
        return $this->pathToFile;
    }

    /** @var string $namespace */
    private string $namespace;
    /**
     * Retorna o namespace do objeto.
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /** @var string $fqn */
    private string $fqn;
    /**
     * Retorna o ``Fully Qualified Name`` do objeto.
     */
    public function getFQN(): string
    {
        return $this->fqn;
    }

    /** @var string $name */
    private string $name;
    /**
     * Retorna o name do objeto.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /** @var ElementType $type */
    private ElementType $type;
    /**
     * Retorna o tipo do objeto.
     */
    public function getType(): ElementType
    {
        return $this->type;
    }





    /**
     * Inicia um novo objeto ``ClassMapData``
     *
     * @param string $pathToFile
     * @param string $fqn
     * @param ElementType $type
     *
     * @throws FileNotFoundException
     */
    function __construct(
        string $pathToFile,
        string $fqn,
        ElementType $type
    ) {
        if (is_file($pathToFile) === false) {
            throw new FileNotFoundException();
        }

        $splitFQN = \explode("\\", $fqn);

        $this->pathToFile = $pathToFile;
        $this->namespace = \implode("\\", \array_slice($splitFQN, 0, -1));
        $this->fqn = $fqn;
        $this->name = \end($splitFQN);
        $this->type = $type;

        if ($this->type === ElementType::UNKNOW) {
            if (interface_exists($fqn) === true) {
                $this->type = ElementType::INTERFACE;
            } elseif (enum_exists($fqn) === true) {
                $this->type = ElementType::ENUM;
            } elseif (trait_exists($fqn) === true) {
                $this->type = ElementType::TRAIT;
            } elseif (class_exists($fqn) === true) {
                $this->type = ElementType::CLASSE;
            }
        }
    }





    /**
     * Retorna um array associativo representando o objeto e todos
     * seus membros que serão usados para criação da view de documentação.
     *
     * @return array
     */
    public function toArray(): array
    {

        $r = [
            "pathToFile"    => $this->pathToFile,
            "namespace"     => $this->namespace,
            "fqn"           => $this->fqn,
            "name"          => $this->name,
            "type"          => $this->type->value,

            "docBlock"      => null,


            "interfaces"    => null,
            "extends"       => null,

            "isAbstract"    => null,
            "isFinal"       => null,

            "constructor"       => null,

            "constants"         => [],

            "staticProperties"  => [],
            "publicProperties"  => [],

            "staticMethods"     => [],
            "abstractMethods"   => [],
            "publicMethods"     => []
        ];



        if (
            $this->type === ElementType::INTERFACE ||
            $this->type === ElementType::ENUM ||
            $this->type === ElementType::TRAIT ||
            $this->type === ElementType::CLASSE
        ) {
            $objReflect = new \ReflectionClass($this->fqn);


            $r["docBlock"] = (new DocBlock($objReflect->getDocComment()))->getRawDocBlockTags();


            $r["interfaces"] = $objReflect->getInterfaceNames();
            if ($r["interfaces"] === []) {
                $r["interfaces"] = null;
            }
            $r["extends"] = $objReflect->getExtensionName();
            if ($r["extends"] === false) {
                $r["extends"] = null;
            }

            $r["isAbstract"] = $objReflect->isAbstract();
            $r["isFinal"] = $objReflect->isFinal();


            $publicConstants = $objReflect->getConstants(\ReflectionClassConstant::IS_PUBLIC);
            $r["constants"] = $publicConstants;

            foreach ($objReflect->getProperties() as $prop) {
                if ($prop->isPublic() === true) {
                    if ($prop->isStatic() === true) {
                        $r["staticProperties"][$prop->getName()] = $this->propertieToArray($prop);
                    } else {
                        $r["publicProperties"][$prop->getName()] = $this->propertieToArray($prop);
                    }
                }
            }


            foreach ($objReflect->getMethods() as $method) {
                if ($method->isPublic() === true) {

                    if ($method->isConstructor() === true) {
                        $r["constructor"] = $this->methodToArray($method);
                    } else {
                        if ($method->isStatic() === true) {
                            $r["staticMethods"][$method->getName()] = $this->methodToArray($method);
                        } elseif ($method->isStatic() === true) {
                            $r["abstractMethods"][$method->getName()] = $this->methodToArray($method);
                        } else {
                            $r["publicMethods"][$method->getName()] = $this->methodToArray($method);
                        }
                    }
                }
            }
        }

        return $r;
    }




    /**
     * A partir de um objeto de tipo passado retorna uma string que o represente.
     *
     * @return \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $objType
     * Objeto de tipo original.
     *
     * @return string
     */
    protected function typeToString(
        \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $objType
    ): string {
        $r = [];

        if ($objType !== null) {
            if ($objType instanceof \ReflectionNamedType) {
                $r[] = $objType->getName();
            } elseif ($objType instanceof \ReflectionUnionType) {
                foreach ($objType->getTypes() as $type) {
                    if ($type instanceof \ReflectionIntersectionType) {
                        $r[] = $this->typeToString($type);
                    } else {
                        if ($type->getName() !== "null") {
                            $r[] = $type->getName();
                        }
                    }
                }
            } elseif ($objType instanceof \ReflectionIntersectionType) {
                $i = [];
                /** @var \ReflectionNamedType &type */
                foreach ($objType->getTypes() as $type) {
                    $i[] = $type->getName();
                }
                $r[] = \implode("&", $i);
            }

            if ($objType->allowsNull() === true) {
                $r[] = "null";
            }
        }

        return \implode("|", $r);
    }
    /**
     * Resgata as informações para documentação da propriedade alvo.
     *
     * @return array
     */
    protected function propertieToArray(\ReflectionProperty $prop): array
    {
        $dValue = null;
        if ($prop->isDefault() === true) {
            $dValue =  $prop->getDefaultValue();
            if (\is_object($dValue) === true) {
                $dValue = \get_class($dValue);
            }
        }

        return [
            "docBlock" => (new DocBlock($prop->getDocComment()))->getRawDocBlockTags(),
            "type" => $this->typeToString($prop->getType()),
            "isDefaultValue" => $prop->isDefault(),
            "defaultValue" => $dValue
        ];
    }
    /**
     * Resgata as informações para documentação do método alvo.
     *
     * @return array
     */
    protected function methodToArray(\ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $par) {
            $parameters[$par->getName()] = $this->parameterToArray($par);
        }

        $docBlock = (new DocBlock($method->getDocComment()))->getRawDocBlockTags();
        if (\key_exists("param", $docBlock["tags"]) === true) {
            foreach ($docBlock["tags"]["param"] as $docBlockParamLines) {
                $paramData = DocBlock::prepareParameterData($docBlockParamLines);
                if ($paramData[0] !== null && \key_exists($paramData[0], $parameters) === true) {
                    $parameters[$paramData[0]]["docBlock"] = $paramData[1];
                }
            }
        }


        return [
            "docBlock" => $docBlock,
            "isAbstract" => $method->isAbstract(),
            "isFinal" => $method->isFinal(),
            "parameters" => $parameters,
            "return" => $this->typeToString($method->getReturnType())
        ];
    }
    /**
     * Resgata as informações para documentação do parametro alvo.
     *
     * @return array
     */
    protected function parameterToArray(\ReflectionParameter $par): array
    {
        $t = $this->typeToString($par->getType());
        $isNullable = str_ends_with($t, "|null");
        if ($t === "mixed|null") {
            $t = "mixed";
        }

        $dValue = null;
        if ($par->isDefaultValueAvailable() === true) {
            $dValue =  $par->getDefaultValue();
            if (\is_object($dValue) === true) {
                $dValue = "instance of " . \get_class($dValue);
            }
        }

        return [
            "type" => $t,
            "docBlock" => [],
            "isOptional" => $par->isOptional(),
            "isReference" => $par->isPassedByReference(),
            "isVariadic" => $par->isVariadic(),
            "isNullable" => $isNullable,
            "isDefaultValue" => $par->isDefaultValueAvailable(),
            "defaultValue" => $dValue
        ];
    }
}