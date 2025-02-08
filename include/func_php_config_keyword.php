<?php

function saveKeywordSettings(): bool
{
    $keyword1Text           = $_REQUEST['keyword1Text'] ?? '';
    $keyword1Cmd            = $_REQUEST['keyword1Cmd'] ?? '';
    $keyword1Enabled        = $_REQUEST['keyword1Enabled'] ?? 0;
    $keyword1ReturnMsg      = $_REQUEST['keyword1ReturnMsg'] ?? '';
    $keyword1DmGrpId        = $_REQUEST['keyword1DmGrpId'] ?? '*';
    $keyword1DmGrpId        = $keyword1DmGrpId == '' ? '*' : $keyword1DmGrpId;
    setParamData('keyword1Text', trim($keyword1Text), 'txt');
    setParamData('keyword1Cmd', trim($keyword1Cmd), 'txt');
    setParamData('keyword1Enabled', $keyword1Enabled);
    setParamData('keyword1ReturnMsg', trim($keyword1ReturnMsg), 'txt');
    setParamData('keyword1DmGrpId', trim($keyword1DmGrpId), 'txt');

    $keyword2Text           = $_REQUEST['keyword2Text'] ?? '';
    $keyword2Cmd            = $_REQUEST['keyword2Cmd'] ?? '';
    $keyword2Enabled        = $_REQUEST['keyword2Enabled'] ?? 0;
    $keyword2ReturnMsg      = $_REQUEST['keyword2ReturnMsg'] ?? '';
    $keyword2DmGrpId        = $_REQUEST['keyword2DmGrpId'] ?? '*';
    $keyword2DmGrpId        = $keyword2DmGrpId == '' ? '*' : $keyword2DmGrpId;
    setParamData('keyword2Text', trim($keyword2Text), 'txt');
    setParamData('keyword2Cmd', trim($keyword2Cmd), 'txt');
    setParamData('keyword2Enabled', $keyword2Enabled);
    setParamData('keyword2ReturnMsg', trim($keyword2ReturnMsg), 'txt');
    setParamData('keyword2DmGrpId', trim($keyword2DmGrpId), 'txt');

    return true;
}
