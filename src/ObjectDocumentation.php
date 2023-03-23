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


    /** @var bool $isClassMember */
    private bool $isClassMember = true;

    /** @var mixed $tmpObjectValue */
    private mixed $tmpObjectValue = true;

    /** @var array $standaloneFileMetaObjects */
    private array $standaloneFileMetaObjects = [];




    /**
     * Inicia um novo objeto ``ObjectDocumentation``
     *
     * @param string $fileName
     * Caminho completo até o arquivo que descreve este objeto.
     *
     * @param string $fqsen
     * Nome completo do objeto.
     * ``Fully Qualified Structural Element Name``.
     * Se não for definido será considerado que trata-se de um arquivo de objetos avulsos.
     *
     * @param ElementType $type
     * Tipo do objeto.
     *
     * @throws FileNotFoundException
     * Caso o arquivo indicado não exista.
     *
     * @throws InvalidArgumentException
     * Caso seja informado um arquivo que não tenha extensão .php
     */
    function __construct(
        string $fileName,
        string $fqsen,
        ElementType $type
    ) {
        if (\is_file($fileName) === false) {
            throw new FileNotFoundException("File not found. [ $fileName ]");
        }
        if (\str_ends_with($fileName, ".php") === false) {
            throw new \InvalidArgumentException(
                "Invalid file extension. Only \".php\" files are allowed [ $fileName ]"
            );
        }


        $splitFQSEN = \explode("\\", $fqsen);

        $this->fileName = $fileName;
        $this->namespaceName = \implode("\\", \array_slice($splitFQSEN, 0, -1));
        $this->fqsen = $fqsen;
        $this->shortName = \end($splitFQSEN);
        $this->type = $type;


        if ($this->type === ElementType::UNKNOW) {
            if ($this->fqsen === "") {
                $this->standaloneFileMetaObjects = DocBlock::parseStandaloneFileToMetaObjects($this->fileName);
                $this->namespaceName = $this->standaloneFileMetaObjects["namespaceName"];
            } else {
                if (\interface_exists($fqsen) === true) {
                    $this->type = ElementType::INTERFACE;
                } elseif (\enum_exists($fqsen) === true) {
                    $this->type = ElementType::ENUM;
                } elseif (\trait_exists($fqsen) === true) {
                    $this->type = ElementType::TRAIT;
                } elseif (\class_exists($fqsen) === true) {
                    $this->type = ElementType::CLASSE;
                }


                // Identifica se trata-se de uma constante
                if ($this->type === ElementType::UNKNOW) {
                    // Primeiro verifica se faz parte de uma classe.
                    if (\defined($this->namespaceName . "::" .  $this->shortName)) {
                        $this->type = ElementType::CONSTANT;
                    } else {
                        // Senão, busca o objeto nas definições gerais de constantes.
                        $gConsts = get_defined_constants(true);
                        $fqsenConst = $this->shortName;
                        if ($this->namespaceName !== "") {
                            $fqsenConst = $this->namespaceName . "\\" . $this->shortName;
                        }

                        if (
                            \key_exists("user", $gConsts) === true &&
                            \key_exists($fqsenConst, $gConsts["user"]) === true
                        ) {
                            $this->type = ElementType::CONSTANT;
                            $this->isClassMember = false;
                            $this->tmpObjectValue = $gConsts["user"][$fqsenConst];
                        }
                    }
                }


                // Identifica se trata-se de uma propriedade de uma classe
                if (
                    $this->type === ElementType::UNKNOW &&
                    \property_exists($this->namespaceName, $this->shortName) === true
                ) {
                    $this->type = ElementType::PROPERTIE;
                }


                // Identifica se trata-se de um método de uma classe
                if (
                    $this->type === ElementType::UNKNOW &&
                    \method_exists($this->namespaceName, $this->shortName) === true
                ) {
                    $this->type = ElementType::METHOD;
                }


                // Identifica se trata-se de uma função
                if (
                    $this->type === ElementType::UNKNOW &&
                    \function_exists($this->namespaceName . "\\" . $this->shortName) === true
                ) {
                    $this->type = ElementType::FUNCTION;
                    $this->isClassMember = false;
                }
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

        if ($this->type !== ElementType::UNKNOW && $this->fqsen !== "") {
            $r = $this->objectToArray();
        } elseif ($this->type === ElementType::UNKNOW && $this->fqsen === "") {
            $r = $this->standaloneFileToArray();
        }

        return $r;
    }



    /**
     * Efetua a conversão das informações de objetos conhecidos (capazes de serem trabalhados
     * via Reflection) para um array associativo.
     *
     * @return array
     */
    protected function objectToArray(): array
    {
        $r = [
            "fileName"          => $this->fileName,
            "namespaceName"     => $this->namespaceName,
            "fqsen"             => $this->fqsen,
            "shortName"         => $this->shortName,
            "type"              => $this->type->value,

            "docBlock"          => []
        ];

        $objReflection = null;
        $arrUseProperties = [];
        $refDocBlock = "";


        switch ($this->type) {
            case ElementType::CONSTANT:
                if ($this->isClassMember === true) {
                    $objReflection = new \ReflectionClassConstant($this->namespaceName, $this->shortName);
                }
                break;

            case ElementType::PROPERTIE:
                $objReflection = new \ReflectionProperty($this->namespaceName, $this->shortName);
                break;

            case ElementType::FUNCTION:
                $objReflection = new \ReflectionFunction($this->namespaceName . "\\" . $this->shortName);
                break;
            case ElementType::METHOD:
                $objReflection = new \ReflectionMethod($this->namespaceName, $this->shortName);
                $arrUseProperties = [
                    "isAbstract", "isFinal"
                ];
                break;

            case ElementType::ENUM:
            case ElementType::INTERFACE:
            case ElementType::TRAIT:
            case ElementType::CLASSE:
                $objReflection = new \ReflectionClass($this->fqsen);
                $arrUseProperties = [
                    "interfaces", "extends", "isAbstract", "isFinal",
                    "constants", "properties", "constructor", "methods"
                ];
                break;
        }


        if ($objReflection !== null) {
            $refDocBlock = $objReflection->getDocComment();
            if ($refDocBlock === false) {
                $refDocBlock = "";
            }
            $r["docBlock"] = DocBlock::fullParseDocBlock($refDocBlock);



            if ($this->type === ElementType::FUNCTION || $this->type === ElementType::METHOD) {
                $r["parameters"] = [];
                $r["return"] = $this->convertParameterTypeToString($objReflection->getReturnType());


                foreach ($objReflection->getParameters() as $param) {
                    $r["parameters"][$param->getName()] = $this->parameterToArray($param);
                }


                if (\key_exists("param", $r["docBlock"]["tags"]) === true) {
                    foreach ($r["docBlock"]["tags"]["param"] as $docBlockParamLines) {
                        $paramData = DocBlock::parseRawDocBlockParamLines($docBlockParamLines);
                        if ($paramData[0] !== null && \key_exists($paramData[0], $r["parameters"]) === true) {
                            $r["parameters"][$paramData[0]]["docBlock"] = $paramData[1];
                        }
                    }
                }
            }



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
                        $r["constants"] = [
                            "public" => []
                        ];

                        $refConstants = $objReflection->getConstants(\ReflectionClassConstant::IS_PUBLIC);
                        if ($refConstants !== null && \count($refConstants) > 0) {
                            foreach ($refConstants as $constantName => $constantValue) {
                                $doc = (new ObjectDocumentation(
                                    $this->fileName,
                                    $this->fqsen . "\\" . $constantName,
                                    ElementType::CONSTANT
                                ))->toArray();
                                $doc["value"] = $this->valueToArray($constantValue);

                                $r["constants"]["public"][] = $doc;
                            }
                        }

                        break;

                    case "properties":
                        $r["properties"] = [
                            "public" => [
                                "static" => [],
                                "nonstatic" => [],
                            ]
                        ];

                        $refProperties = $objReflection->getProperties();
                        foreach ($refProperties as $objProp) {
                            if ($objProp->isPublic() === true) {
                                $doc = (new ObjectDocumentation(
                                    $this->fileName,
                                    $this->fqsen . "\\" . $objProp->getName(),
                                    ElementType::PROPERTIE
                                ))->toArray();
                                $doc["defaultValue"] = $this->valueToArray($objProp->getDefaultValue());

                                if ($objProp->isStatic() === true) {
                                    $r["properties"]["public"]["static"][] = $doc;
                                } else {
                                    $r["properties"]["public"]["nonstatic"][] = $doc;
                                }
                            }
                        }

                        break;

                    case "constructor":
                        $r["constructor"] = null;

                        if ($objReflection->getConstructor() !== null) {
                            $doc = (new ObjectDocumentation(
                                $this->fileName,
                                $this->fqsen . "\\__construct",
                                ElementType::METHOD
                            ))->toArray();

                            $r["constructor"] = $doc;
                        }
                        break;

                    case "methods":
                        $r["methods"] = [
                            "public" => [
                                "abstract" => [
                                    "static" => [],
                                    "nonstatic" => []
                                ],
                                "nonabstract" => [
                                    "static" => [],
                                    "nonstatic" => [],
                                ]
                            ]
                        ];

                        $refMethods = $objReflection->getMethods();
                        foreach ($refMethods as $objMethod) {
                            if ($objMethod->isPublic() === true && $objMethod->isConstructor() === false) {
                                $doc = (new ObjectDocumentation(
                                    $this->fileName,
                                    $this->fqsen . "\\" . $objMethod->getName(),
                                    ElementType::METHOD
                                ))->toArray();


                                if ($objMethod->isAbstract() === true) {
                                    if ($objMethod->isStatic() === true) {
                                        $r["methods"]["public"]["abstract"]["static"][] = $doc;
                                    } else {
                                        $r["methods"]["public"]["abstract"]["nonstatic"][] = $doc;
                                    }
                                } else {
                                    if ($objMethod->isStatic() === true) {
                                        $r["methods"]["public"]["nonabstract"]["static"][] = $doc;
                                    } else {
                                        $r["methods"]["public"]["nonabstract"]["nonstatic"][] = $doc;
                                    }
                                }
                            }
                        }

                        break;
                }
            }
        }


        return $r;
    }



    /**
     * Efetua a conversão das informações de objetos standalone.
     *
     * @return array
     */
    protected function standaloneFileToArray(): array
    {
        require_once $this->fileName;

        $r = [
            "fileName"          => $this->fileName,
            "namespaceName"     => $this->namespaceName,
            "fqsen"             => $this->fqsen,
            "shortName"         => $this->shortName,
            "type"              => "FILE",

            "constants"         => [],
            "variables"         => [],
            "functions"         => [],
        ];


        $namespaceNameFQSEN = "";
        if ($this->namespaceName !== "") {
            $namespaceNameFQSEN = $this->namespaceName . "\\";
        }


        foreach ($this->standaloneFileMetaObjects["objects"] as $i => $objMetaData) {
            switch ($objMetaData["type"]) {
                case "CONSTANT":
                    $doc = (new ObjectDocumentation(
                        $this->fileName,
                        $namespaceNameFQSEN . $objMetaData["shortName"],
                        ElementType::UNKNOW
                    ))->toArray();
                    $doc["docBlock"] = DocBlock::fullParseDocBlock(\implode("\n", $objMetaData["docBlock"]));
                    $r["constants"][] = $doc;
                    break;

                case "VARIABLE":
                    $r["variables"][] = [
                        "fileName" => $this->fileName,
                        "namespaceName" => $this->namespaceName,
                        "fqsen" => $namespaceNameFQSEN . $objMetaData["shortName"],
                        "shortName" => $objMetaData["shortName"],
                        "type" => "VARIABLE",
                        "docBlock" => DocBlock::fullParseDocBlock(\implode("\n", $objMetaData["docBlock"]))
                    ];
                    break;

                case "FUNCTION":
                    $doc = (new ObjectDocumentation(
                        $this->fileName,
                        $namespaceNameFQSEN . $objMetaData["shortName"],
                        ElementType::UNKNOW
                    ))->toArray();
                    $doc["docBlock"] = DocBlock::fullParseDocBlock(\implode("\n", $objMetaData["docBlock"]));
                    $r["functions"][] = $doc;
                    break;
            }
        }


        return $r;
    }




    /**
     * Analisa o valor passado e retorna um array associativo contendo seu tipo
     * e seu valor atual se este for do tipo 'scalar'. Outros valores virão como
     * uma string vazia.
     *
     * @param mixed $v
     * Valor que será analisado.
     *
     * @return array
     */
    protected function valueToArray(mixed $v): array
    {
        return [
            "type" => \get_type($v),
            "originalValue" => (is_object($v) === true) ? get_class($v) : $v,
            "stringValue" => \convert_value_to_string($v)
        ];
    }





    /**
     * Resgata as informações para documentação do parametro alvo.
     *
     * @param \ReflectionParameter $param
     * Parametro base para coleta de informações.
     *
     * @return array
     */
    protected function parameterToArray(\ReflectionParameter $param): array
    {
        $t = $this->convertParameterTypeToString($param->getType());
        $isNullable = \str_ends_with((string)$t, "|null");
        if ($t === "mixed|null") {
            $t = "mixed";
        }

        $dValue = null;
        if ($param->isDefaultValueAvailable() === true) {
            $dValue =  $param->getDefaultValue();
            if (\is_object($dValue) === true) {
                $dValue = "instance of " . \get_class($dValue);
            }
        }

        return [
            "type" => $t,
            "isOptional" => $param->isOptional(),
            "isReference" => $param->isPassedByReference(),
            "isVariadic" => $param->isVariadic(),
            "isNullable" => $isNullable,
            "isDefaultValue" => $param->isDefaultValueAvailable(),
            "defaultValue" => $dValue,
            "docBlock" => []
        ];
    }
    /**
     * A partir de um objeto de tipo passado retorna uma string que o represente.
     *
     * @return \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $objType
     * Objeto de tipo original.
     *
     * @return string
     */
    private function convertParameterTypeToString(
        \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $objType
    ): string {
        $r = [];

        if ($objType !== null) {
            if ($objType instanceof \ReflectionNamedType) {
                $r[] = $objType->getName();
            } elseif ($objType instanceof \ReflectionUnionType) {
                foreach ($objType->getTypes() as $type) {
                    if ($type instanceof \ReflectionIntersectionType) {
                        $r[] = $this->convertParameterTypeToString($type);
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
}