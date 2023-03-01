<?php

declare(strict_types=1);

namespace AeonDigital\DocBlockExtractor;

use AeonDigital\DocBlockExtractor\Exceptions\FileNotFoundException as FileNotFoundException;
use AeonDigital\DocBlockExtractor\Enums\ElementType as ElementType;
use AeonDigital\DocBlockExtractor\Parser\DocBlock as DocBlock;





/**
 * Classe que agrupa informações de documentação de um objeto.
 *
 * @package     AeonDigital\DocBlockExtractor
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2023, Rianna Cantarelli
 * @license     MIT
 */
class ObjectDocumentation
{



    /** @var string $fileName */
    private string $fileName = "";
    /**
     * Retorna o caminho completo até o arquivo onde este objeto.
     * está declarado.
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /** @var string $namespaceName */
    private string $namespaceName;
    /**
     * Retorna o nome da namespace do objeto.
     */
    public function getNamespaceName(): string
    {
        return $this->namespaceName;
    }

    /** @var string $fqsen */
    private string $fqsen;
    /**
     * Retorna o ``Fully Qualified Structural Element Name`` do objeto.
     */
    public function getFQSEN(): string
    {
        return $this->fqsen;
    }

    /** @var string $shortName */
    private string $shortName;
    /**
     * Retorna o nome curto do objeto.
     */
    public function getShortName(): string
    {
        return $this->shortName;
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
     * Inicia um novo objeto ``ObjectDocumentation``
     *
     * @param string $fileName
     * Caminho completo até o arquivo que descreve este objeto.
     *
     * @param string $fqsen
     * Nome completo do objeto.
     * ``Fully Qualified Structural Element Name``
     *
     * @param ElementType $type
     * Tipo do objeto.
     *
     * @throws FileNotFoundException
     */
    function __construct(
        string $fileName,
        string $fqsen,
        ElementType $type
    ) {
        if (is_file($fileName) === false) {
            throw new FileNotFoundException("File not found. [ $fileName ]");
        }

        $splitFQSEN = \explode("\\", $fqsen);

        $this->fileName = $fileName;
        $this->namespaceName = \implode("\\", \array_slice($splitFQSEN, 0, -1));
        $this->fqsen = $fqsen;
        $this->shortName = \end($splitFQSEN);
        $this->type = $type;



        if ($this->type === ElementType::UNKNOW) {
            if (interface_exists($fqsen) === true) {
                $this->type = ElementType::INTERFACE;
            } elseif (enum_exists($fqsen) === true) {
                $this->type = ElementType::ENUM;
            } elseif (trait_exists($fqsen) === true) {
                $this->type = ElementType::TRAIT;
            } elseif (class_exists($fqsen) === true) {
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
        $r = [];

        $objReflection = null;
        $arrUseProperties = [];
        $refDocBlock = "";


        if ($this->type !== ElementType::UNKNOW) {
            switch ($this->type) {
                case ElementType::CONSTANT:
                    $objReflection = new \ReflectionClassConstant($this->namespaceName, $this->shortName);
                    break;

                case ElementType::VARIABLE:
                    break;
                case ElementType::PROPERTIE:
                    break;

                case ElementType::FUNCTION:
                    break;
                case ElementType::METHOD:
                    break;

                case ElementType::INTERFACE:
                    break;
                case ElementType::ENUM:
                    break;
                case ElementType::TRAIT:
                    break;
                case ElementType::CLASSE:
                    $objReflection = new \ReflectionClass($this->fqsen);
                    $arrUseProperties = [
                        "interfaces", "extends", "isAbstract", "isFinal",
                        "constants"
                    ];
                    break;
            }


            $refDocBlock = $objReflection->getDocComment();
            if ($refDocBlock === false) {
                $refDocBlock = "";
            }

            $r = [
                "fileName"          => $this->fileName,
                "namespaceName"     => $this->namespaceName,
                "fqsen"             => $this->fqsen,
                "shortName"         => $this->shortName,
                "type"              => $this->type->value,

                "docBlock"          => DocBlock::fullParseDocBlock($refDocBlock)
            ];



            foreach ($arrUseProperties as $propName) {
                switch ($propName) {
                    case "interfaces":
                        $propValue = $objReflection->getInterfaceNames();
                        if ($propValue === []) {
                            $propValue = null;
                        }
                        $r["interfaces"] = $propValue;
                        break;

                    case "extends":
                        $propValue = $objReflection->getExtensionName();
                        if ($propValue === false) {
                            $propValue = null;
                        }
                        $r["extends"] = $propValue;
                        break;

                    case "isAbstract":
                        $r["isAbstract"] = $objReflection->isAbstract();
                        break;

                    case "isFinal":
                        $r["isFinal"] = $objReflection->isFinal();
                        break;

                    case "constants":
                        $r["constants"] = [];

                        $refConstants = $objReflection->getConstants(\ReflectionClassConstant::IS_PUBLIC);
                        if ($refConstants !== null && \count($refConstants) > 0) {
                            foreach ($refConstants as $constantName => $constantValue) {
                                $r["constants"][] = (new ObjectDocumentation(
                                    $this->fileName,
                                    $this->fqsen . "\\" . $constantName,
                                    ElementType::CONSTANT
                                ))->toArray();
                            }
                        }

                        break;
                }
            }
        }

        //"constructor"       => null,
        //"staticProperties"  => [],
        //"publicProperties"  => [],
        //"staticMethods"     => [],
        //"abstractMethods"   => [],
        //"publicMethods"     => []


        /*
        foreach ($this->objReflection->getProperties() as $prop) {
            if ($prop->isPublic() === true) {
                if ($prop->isStatic() === true) {
                    $r["staticProperties"][$prop->getName()] = $this->propertieToArray($prop);
                } else {
                    $r["publicProperties"][$prop->getName()] = $this->propertieToArray($prop);
                }
            }
        }


        foreach ($this->objReflection->getMethods() as $method) {
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
        }*/


        return $r;
    }




    /**
     * A partir de um objeto de tipo passado retorna uma string que o represente.
     *
     * @return \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $objType
     * Objeto de tipo original.
     *
     * @return string
     *
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
                /** @var \ReflectionNamedType &type * /
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
     *
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
     *
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
     *
    protected function parameterToArray(\ReflectionParameter $par): array
    {
        $t = $this->typeToString($par->getType());
        $isNullable = \str_ends_with((string)$t, "|null");
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
     */
}