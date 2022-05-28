<?php

const DEFAULT_JSON_ENCODING_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

const FILE_SIZE_UNIT_MB = 1048576;
const FILE_SIZE_UNIT_KB = 1024;

const FILE_UPLOAD_ALLOW_MIMETYPE = [
    'image/png',
    'image/gif',
    'image/jpeg',
    'video/mp4',
    'application/msword', // word
    'application/vnd.ms-excel', // xls
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
];
