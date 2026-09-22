<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brazilian Portuguese strings for local_coursestartnotify.
 *
 * @package    local_coursestartnotify
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Notificações de início do curso';
$string['enabled'] = 'Ativar notificações';
$string['enabled_desc'] = 'Envia uma notificação aos usuários com matrícula ativa quando a data de início do curso chegar.';
$string['lookbackhours'] = 'Janela de verificação (horas)';
$string['lookbackhours_desc'] = 'Em cada execução são verificados os cursos que iniciaram dentro desta quantidade de horas. Os envios concluídos ficam registrados e não são duplicados. A data de instalação é sempre respeitada, portanto a instalação do plugin não dispara notificações de cursos históricos.';
$string['notifyhidden'] = 'Notificar cursos ocultos';
$string['notifyhidden_desc'] = 'Quando ativado, o usuário pode receber a notificação mesmo se o curso estiver oculto. Deixe desativado a menos que a visibilidade do curso seja controlada separadamente no lançamento.';
$string['subject'] = 'Assunto personalizado';
$string['subject_desc'] = 'Deixe vazio para usar o texto padrão traduzido. Placeholders disponíveis: {firstname}, {coursename}, {startdate}, {courseurl}.';
$string['body'] = 'Mensagem personalizada';
$string['body_desc'] = 'Deixe vazio para usar o texto padrão traduzido. Placeholders disponíveis: {firstname}, {coursename}, {startdate}, {courseurl}.';
$string['retentiondays'] = 'Retenção do histórico de envio (dias)';
$string['retentiondays_desc'] = 'Por quanto tempo manter o registro usado para impedir notificações duplicadas. Mínimo de 30 dias.';
$string['task_sendnotifications'] = 'Enviar notificações de início do curso';
$string['messageprovider:coursestart'] = 'Notificação de início do curso';
$string['notificationsubject'] = 'Seu curso {$a->coursename} já está disponível';
$string['notificationbody'] = 'Olá {$a->firstname},\n\nO curso "{$a->coursename}" chegou à data de início ({$a->startdate}) e já está disponível para você.\n\nAcesse o curso: {$a->courseurl}';
$string['privacy:metadata:log'] = 'Armazena quais notificações de início de curso foram enviadas para impedir o reenvio repetido ao mesmo usuário.';
$string['privacy:metadata:log:courseid'] = 'O curso que originou a notificação.';
$string['privacy:metadata:log:userid'] = 'O usuário que recebeu a notificação.';
$string['privacy:metadata:log:startdate'] = 'A data de início do curso que originou esta notificação.';
$string['privacy:metadata:log:timesent'] = 'A data e hora em que a notificação foi enviada.';
$string['privacy:path'] = 'Notificações de início do curso';
