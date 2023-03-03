<?php

declare(strict_types=1);

namespace AeonDigital\Interfaces;









/**
 * Interface para tipo numérico "Real".
 * Nas classes concretas deve usar alguma extenção matemática como **BC Math**.
 *
 * @package     AeonDigital\Interfaces
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2022, Rianna Cantarelli
 * @license     MIT
 */
interface iRealType
{



    /**
     * Retorna o valor que esta instância está representando.
     *
     * @return string
     */
    public function value(): string;

    /**
     * Retorna apenas a parte inteira do numeral representado por esta instância.
     *
     * @return string
     */
    public function getIntegerPart(): string;

    /**
     * Retorna apenas a parte decimal do numeral representado por esta instância.
     *
     * @return string
     */
    public function getDecimalPart(): string;

    /**
     * Retorna o total de dígitos que compõe este numeral somando o total de casas antes e após
     * o separador decimal.
     *
     * @return int
     */
    public function precision(): int;

    /**
     * Retorna o total de digitos que são usados para representar a parte inteira do numeral
     * atual.
     *
     * @return int
     */
    public function integerPlaces(): int;

    /**
     * Retorna o total de digitos que são usados para representar a parte decimal do numeral
     * atual.
     *
     * @return int
     */
    public function decimalPlaces(): int;





    /**
     * Permite definir um valor padrão para o argumento ``$dPlaces`` usado em vários métodos
     * desta classe.
     *
     * Quando algum método que usa o argumento ``$dPlaces`` for igual a ``null``, o valor aqui
     * definido é que será usado.
     *
     * @param int $v
     * Valor padrão a ser usado.
     *
     * @return void
     */
    public static function defineGlobalDecimalPlaces(int $v): void;

    /**
     * Retorna o número de casas decimais sendo usadas no momento para fins de cálculos com esta
     * classe.
     *
     * @return int
     */
    public static function getGlobalDecimalPlaces(): int;

    /**
     * Define a forma padrão pela qual os valores, quando calculados, serão arredondados.
     *
     * @param ?string $roundType
     * Indica o tipo de arredondamento que será feito.
     * Valores inválidos não incorrerão em erros e nem em nenhuma conversão.
     *
     * Os valores aceitos são:
     * - ``floor``   :  Arredondará para baixo qualquer valor a partir do **digito sensível**.
     * - ``ceil``    :  Arredondará para cima todo valor diferente de zero a partir do **digito sensível**.
     * - ``floor-n`` :  Arredondará para baixo todo **digito sensível** que seja igual ou menor que ``n`` e
     *                  para cima todo **digito sensível** maior que ``n``.
     * - ``ceil-n``  :  Arredondará para cima todo **digito sensível** que seja igual ou maior que ``n`` e
     *                  para baixo todo **digito sensível** menor que ``n``.
     *
     * @param iRealType $sensibility
     * A sensibilidade é sempre um valor que indica qual será exatamente o digito que será sensível ao
     * arredondamento.
     *
     * Por exemplo: ``0.001`` fará o arredondamento do número a partir do 3º digito após
     * o ponto decimal enquanto ``10`` fará o arredondamento das casas das dezenas.
     *
     * @return void
     */
    public static function defineGlobalRoundType(?string $roundType, ?iRealType $sensibility): void;

    /**
     * Retorna o tipo de arredondamento definido para os cálculos realizados com esta classe.
     *
     * @return ?string
     */
    public static function getRoundType(): ?string;

    /**
     * Retorna o nível de sensibilidade usada para os arredondamentos.
     *
     * @return ?iRealType
     */
    public static function getRoundSensibility(): ?iRealType;

    /**
     * Identifica se o valor passado é um ``iRealType`` válido.
     *
     * @param mixed $v
     * Valor a ser testado.
     *
     * @return bool
     * Retorna ``true`` se o valor passado for válido.
     */
    public static function isValidRealtype(mixed $v): bool;





    /**
     * Verifica se o valor atual desta instância é igual ao valor passado para comparação.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para comparação.
     *
     * @return bool
     * Retorna ``true`` se o valor atual desta instância e o valor passado em ``$v``
     * forem **IDÊNTICOS**.
     */
    public function isEqualAs(int|float|string|iRealType $v): bool;

    /**
     * Verifica se o valor atual desta instância é maior que o valor passado para comparação.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para comparação.
     *
     * @return bool
     * Retornará ``true`` se o valor atual desta instância é **MAIOR** que o valor
     * indicado em ``$v``.
     */
    public function isGreaterThan(int|float|string|iRealType $v): bool;

    /**
     * Verifica se o valor atual desta instância é maior ou igual ao valor passado para comparação.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para comparação.
     *
     * @return bool
     * Retornará ``true`` se o valor atual desta instância é **MAIOR** ou **IGUAL**
     * ao o valor indicado em ``$v``.
     */
    public function isGreaterOrEqualAs(int|float|string|iRealType $v): bool;

    /**
     * Verifica se o valor atual desta instância é menor que o valor passado para comparação.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para comparação.
     *
     * @return bool
     * Retornará ``true`` se o valor atual desta instância é **MENOR** que o valor
     * indicado em ``$v``.
     */
    public function isLessThan(int|float|string|iRealType $v): bool;

    /**
     * Verifica se o valor atual desta instância é menor ou igual ao valor passado para comparação.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para comparação.
     *
     * @return bool
     * Retornará ``true`` se o valor atual desta instância é **MENOR** ou **IGUAL**
     * ao o valor indicado em ``$v``.
     */
    public function isLessOrEqualAs(int|float|string|iRealType $v): bool;





    /**
     * Verifica se o valor atual desta instância é ``zero``.
     *
     * @return bool
     * Retornará ``true`` se o valor atual desta instância for ``zero``.
     */
    public function isZero(): bool;

    /**
     * Verifica se o valor atual desta instância é um número positivo.
     *
     * @return bool
     * Retornará ``true`` se o valor atual desta instância for um número positivo.
     */
    public function isPositive(): bool;

    /**
     * Verifica se o valor atual desta instância é um número negativo.
     *
     * @return bool
     * Retornará ``true`` se o valor atual desta instância for um número negativo.
     */
    public function isNegative(): bool;

    /**
     * Retorna uma nova instância ``iRealType`` com o mesmo valor atual desta instância mas com
     * o sinal positivo.
     *
     * @return iRealType
     */
    public function toPositive(): iRealType;

    /**
     * Retorna uma nova instância ``iRealType`` com o mesmo valor atual desta instância mas com
     * o sinal negativo.
     *
     * @return iRealType
     */
    public function toNegative(): iRealType;

    /**
     * Retorna uma nova instância ``iRealType`` com o mesmo valor atual desta instância mas com
     * o sinal invertido.
     *
     * @return iRealType
     */
    public function invertSignal(): iRealType;





    /**
     * Efetua o arredondamento de valores conforme as regras indicadas.
     *
     * @param iRealType $v
     * Valor que será arredondado.
     *
     * @param string $roundType
     * Indica o tipo de arredondamento que será feito.
     * Valores inválidos não incorrerão em erros e nem em nenhuma conversão.
     *
     * Os valores aceitos são:
     * - ``floor``   :  Arredondará para baixo qualquer valor a partir do **digito sensível**.
     * - ``ceil``    :  Arredondará para cima todo valor diferente de zero a partir do **digito sensível**.
     * - ``floor-n`` :  Arredondará para baixo todo **digito sensível** que seja igual ou menor que ``n`` e
     *                  para cima todo **digito sensível** maior que ``n``.
     * - ``ceil-n``  :  Arredondará para cima todo **digito sensível** que seja igual ou maior que ``n`` e
     *                  para baixo todo **digito sensível** menor que ``n``.
     *
     * @param iRealType $sensibility
     * A sensibilidade é sempre um valor que indica qual será exatamente o digito que será sensível ao arredondamento.
     *
     * Por exemplo:
     *
     * ``0.001`` fará o arredondamento do número a partir do 3º digito após o ponto decimal enquanto ``10`` fará o
     * arredondamento das casas das dezenas.
     *
     * @return iRealType
     * Nova instância ``iRealType`` com o resultado do arredondamento indicado.
     */
    public static function roundTo(iRealType $v, string $roundType, iRealType $sensibility): iRealType;





    /**
     * Efetua uma adição do valor atual desta instância com o valor indicado.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para o cálculo.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @return iRealType
     * Nova instância com o resultado desta operação.
     */
    public function sum(int|float|string|iRealType $v, ?int $dPlaces = null): iRealType;

    /**
     * Efetua uma subtração do valor atual desta instância com o valor indicado.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para o cálculo.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @return iRealType
     * Nova instância com o resultado desta operação.
     */
    public function sub(int|float|string|iRealType $v, ?int $dPlaces = null): iRealType;

    /**
     * Efetua uma multiplicação do valor atual desta instância com o valor indicado.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para o cálculo.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @return iRealType
     * Nova instância com o resultado desta operação.
     */
    public function mul(int|float|string|iRealType $v, ?int $dPlaces = null): iRealType;

    /**
     * Efetua uma divisão do valor atual desta instância com o valor indicado.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para o cálculo.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @return iRealType
     * Nova instância com o resultado desta operação.
     */
    public function div(int|float|string|iRealType $v, ?int $dPlaces = null): iRealType;

    /**
     * Calcula o módulo da divisão do valor atual desta instância pelo valor indicado.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para o cálculo.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @return iRealType
     * Nova instância com o resultado desta operação.
     */
    public function mod(int|float|string|iRealType $v, ?int $dPlaces = null): iRealType;

    /**
     * Eleva o valor atual desta instância pelo expoente indicado.
     *
     * @param int|float|string|iRealType $v
     * Valor usado para o cálculo.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @return iRealType
     * Nova instância com o resultado desta operação.
     */
    public function pow(int|float|string|iRealType $v, ?int $dPlaces = null): iRealType;

    /**
     * Calcula a raiz quadrada do valor atual desta instância.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @return iRealType
     * Raiz quadrada do valor atual desta instância.
     */
    public function sqrt(?int $dPlaces = null): iRealType;





    /**
     * Configura a forma como uma instância deve se comportar quando forçada a ser convertida
     * para uma ``string``.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Permite definir um novo objeto baseado no estado completo passado pelo parametro ``$state``.
     *
     * @param array $state
     * Dados que serão adicionados ao novo objeto.
     *
     * @return iRealType
     * Nova instância preenchida com os valores do estado indicado em ``$state``.
     */
    public static function __set_state(array $state): iRealType;

    /**
     * Formata o valor atual desta instância usando o pontuador decimal e de milhar indicados.
     *
     * @param ?int $dPlaces
     * Total de casas decimais a serem levadas em conta.
     * Se ``null`` for passado, usará o padrão definido em ``static::$globalDecimalPlaces``.
     *
     * @param string $dec
     * Pontuador decimal a ser usado.
     *
     * @param string $tho
     * Pontuador de milhar a ser usado.
     *
     * @return string
     * Valor atual desta instância formatado conforme definido.
     */
    public function format(?int $dPlaces = null, string $dec = "", string $tho = ""): string;
}
