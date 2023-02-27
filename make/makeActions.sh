#!/usr/bin/env bash
# myShellEnv v 1.0 [aeondigital.com.br]







#
# Carrega as ferramentas de uso geral
. "${PWD}/.env"
. "${PWD}/make/makeEnvironment.sh"
. "${PWD}/Shell-Make/assets/standalone.sh"
. "${PWD}/Shell-Make/assets/makeTools.sh"





#
# Ação executada imediatamente ANTES cada comando 'make'.
#
# @param string $1
# Recebe o nome do comando que está sendo executado.
#
makeExecuteBefore() {
  if [ "${ENVIRONMENT}" == "UTEST" ]; then
    if [ "$1" == "up" ] && [ ! -f "./composer.lock" ]; then
      echo "{}" > "./composer.lock"
    fi
  fi


  if [ "$1" == "test" ]; then
    if [ "${MK_HAS_DATABASE_CONTAINER}" == "1" ]; then
      . "${PWD}/Shell-Make/modules/docker/makeActions.sh"
      local tmpCheck=$(checkIfContainerExists "${CONTAINER_DBSERVER_NAME}")


      local tmpMsgTitle="ATENÇÃO"
      declare -a arrMessage=()

      if [ "${tmpCheck}" == "0" ]; then
        arrMessage+=("${mseNONE}O container do banco de dados não está ativo!")
        mse_inter_showAlert "a" "${tmpMsgTitle}" "arrMessage"
      else
        . "${PWD}/Shell-Make/modules/database/makeActions.sh"
        tmpCheck=$(dataBaseCheckCredentials)

        if [ "${tmpCheck}" == "0" ]; then
          arrMessage+=("${mseNONE}O container do banco de dados ainda não está pronto para ser usado!")
          arrMessage+=("${mseNONE}Se você o ativou a poucos segundos, aguarde.")
          arrMessage+=("${mseNONE}Tal container demora vários segundos ou até alguns minutos para iniciar todos os serviços.")
          arrMessage+=("${mseNONE}Use o comando abaixo para verificar quando o serviço está pronto.")
          arrMessage+=("${mseYELLOW}make db-check")
          arrMessage+=("")
          arrMessage+=("${mseNONE}Quando receber uma resposta afirmativa, use o comando abaixo para inicar")
          arrMessage+=("${mseNONE}um novo banco de dados totalmente zerado e pronto para os testes")
          arrMessage+=("${mseYELLOW}make db-clean")

          mse_inter_showAlert "a" "${tmpMsgTitle}" "arrMessage"
        else
          arrMessage+=("${mseNONE}Lembre-se que se o container do banco de dados foi iniciado do zero neste momento")
          arrMessage+=("${mseNONE}você precisa executar o seguinte comando para efetuar os testes:")
          arrMessage+=("${mseYELLOW}make db-clean")
          arrMessage+=("")
          arrMessage+=("${mseNONE}Da mesma forma, sempre que precisar restaurar o banco de dados para novos testes, ")
          arrMessage+=("${mseNONE}você pode usá-lo novamente")

          mse_inter_showAlert "a" "${tmpMsgTitle}" "arrMessage"
        fi
      fi
    fi
  fi
}





#
# Ação executada imediatamente ANTES cada comando 'make'.
#
# @param string $1
# Recebe o nome do comando que está sendo executado.
#
makeExecuteAfter() {
  local doNothing=""
}










#
# Permite evocar uma função deste script a partir de um argumento passado ao chamá-lo.
$*
