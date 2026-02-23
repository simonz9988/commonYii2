<?php
/**
 * s3media 的配置文件
 * 除去配置文件外的其他文件不建议修改
 *
 * @author joker.huang@ecovacs.com
 */

return [
    // 允许上传的文件后缀名
    "allowFiles"    => ["jpeg", "png", "gif", "jpg", "mp4", "mpeg", "avi", "3gp", "rm", "rmvb", "mov", "wmv", "flv", "asf"],

    // 允许上传的文件最大大小, 100M
    "maxFileSize"   => 104857600,

    // 调教表单时候的名称
    "fieldName"     => "s3UploadField",
];