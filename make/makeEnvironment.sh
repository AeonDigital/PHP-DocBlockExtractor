#!/usr/bin/env bash
# myShellEnv v 1.0 [aeondigital.com.br]


#
# Configuração do MSE
MSE_GLOBAL_THEME_NAME="mse_inter_theme_default"


#
# Variáveis para comandos Makefile
MK_ENV_FILE="${PWD}/.env"

MK_WEBSERVER_INTERNAL_DATABASE_BOOTSTRAP_FILE="/etc/database/bootstrap.sql"
MK_WEBSERVER_EXTERNAL_DATABASE_BOOTSTRAP_FILE="${CONTAINER_WEBSERVER_CONFIG_DIR}${MK_WEBSERVER_INTERNAL_DATABASE_BOOTSTRAP_FILE}"

#
# Variáveis para controle de comandos relacionados com o Git
MK_GIT_DEFAULT_BRANCH="main"
MK_GIT_DEFAULT_LOG_LENGTH="10"
MK_GIT_TASK_FINISH_MERGE="1"