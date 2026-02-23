<div>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; font-weight:bold"><?=$dmCode_str?></span></p>
    <h1 style="font-size:18pt; line-height:200%; margin:0pt 0pt 10pt; orphans:0; page-break-after:avoid; page-break-inside:avoid; text-align:center; widows:0">
        <span style="font-family:Arial; font-size:18pt; font-weight:bold"><?=$dmTitle['techName']?></span></h1>
    <h1 style="font-size:18pt; line-height:200%; margin:0pt 0pt 10pt; orphans:0; page-break-after:avoid; page-break-inside:avoid; text-align:center; widows:0">
        <span style="font-family:Arial; font-size:18pt; font-weight:bold">-</span>
        <span style="font-family:Arial; font-size:18pt; font-weight:bold"><?=$dmTitle['infoName']?></span></h1>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; font-weight:bold">Common Information</span></p>
    <ol type="1" style="margin:0pt; padding-left:0pt">
        <?php if($commonInfo):?>
        <?php foreach( $commonInfo as $k=>$v):?>
        <li style="font-family:Arial; font-size:14pt; line-height:115%; margin:0pt 0pt 10pt 23.78pt; orphans:0; padding-left:1.32pt; text-indent:0pt; widows:0">
            <span style="font-family:Arial; font-size:14pt; font-weight:normal"><?=$v?> </span>
            </li>
        <?php endforeach ;?>
        <?php endif ;?>
        <li style="font-family:Arial; font-size:14pt; line-height:115%; margin:0pt 0pt 10pt 23.78pt; orphans:0; padding-left:1.32pt; text-indent:0pt; widows:0">
        <?php if($figureTitle):?>
           <a href="<?=$figureUrl?>"><span  style="color:#0070c0; font-family:Arial; font-size:14pt; text-decoration:none"><?=$figureTitle?></span>
           </a>
           <?php endif ;?>
        </li>
    </ol>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; font-weight:bold">Preliminary Requirements</span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Reference</span></p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt; width:426.85pt">
        <tr>
            <td style="background-color:#e7e6e6; border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                    <span style="font-family:Arial; font-size:14pt; font-weight:bold">Data moudle code</span></p>
            </td>
            <td style="background-color:#e7e6e6; border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                    <span style="font-family:Arial; font-size:14pt; font-weight:bold">title</span></p>
            </td>
        </tr>
        <tr>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                    <span style="font-family:Arial; font-size:14pt"><?=$refsDmCodesStr?></span></p>
            </td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
                    <?php if($refsDmAddressTitle):?>
                    <span style="font-family:Arial; font-size:14pt"><?=$refsDmAddressTitle['techName']?></span>
                    <span style="font-family:Arial; font-size:14pt">-</span>
                    <span style="font-family:Arial; font-size:14pt"><?=$refsDmAddressTitle['infoName']?></span></p>
                    <?php endif ;?>
            </td>
        </tr>
    </table>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-indent:14pt; widows:0">
        <span style="color:#c00000; font-family:Arial; font-size:14pt">&#xa0;</span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Zone</span></p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt; width:426.85pt">
        <tr>
            <td style="background-color:#e7e6e6; border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                    <span style="font-family:Arial; font-size:14pt; font-weight:bold">Zone</span></p>
            </td>
            <td style="background-color:#e7e6e6; border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                    <span style="font-family:Arial; font-size:14pt; font-weight:bold">Location</span></p>
            </td>
        </tr>
        <tr>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                    <span style="font-family:Arial; font-size:14pt"><?=$workAreaLocationGroup?$workAreaLocationGroup['zoneRef']['name']:''?></span></p>
            </td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                    <span style="font-family:Arial; font-size:14pt"><?=$workAreaLocationGroup?$workAreaLocationGroup['accessPointRef']['name']:''?></span></p>
            </td>
        </tr>
    </table>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Access panel</span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt 21pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt"><?=$workAreaLocationGroup?$workAreaLocationGroup['accessPointRef']['name']:''?></span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Required Condition</span></p>
    <ol type="1" style="margin:0pt; padding-left:0pt">
        <?php if($reqCondNoRef):?>
        <?php foreach($reqCondNoRef as $v):?>
        <li style="font-family:Arial; font-size:14pt; line-height:115%; margin:0pt 0pt 10pt 37.68pt; orphans:0; padding-left:1.32pt; text-indent:0pt; widows:0">
            <span style="font-family:Arial; font-size:14pt"><?=$v['reqCond']?></span></li>
        <?php endforeach;?>
        <?php endif ;?>
    </ol>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Equipment/Tool</span></p>
    <div style="text-align:center">
        <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin:0 auto; width:426.85pt">
            <tr style="height:31.2pt">
                <td style="background-color:#e7e6e6; border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                        <span style="font-family:Arial; font-size:14pt; font-weight:bold">Name</span></p>
                </td>
                <td style="background-color:#e7e6e6; border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                        <span style="font-family:Arial; font-size:14pt; font-weight:bold">Part number</span></p>
                </td>
            </tr>
            <tr style="height:31.2pt">
                <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                        <span style="font-family:Arial; font-size:14pt"><?=$supportEquipDescr?$supportEquipDescr['name']:''?></span></p>
                </td>
                <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:202.25pt">
                    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
                        <span style="font-family:Arial; font-size:14pt"><?=$supportEquipDescr?$supportEquipDescr['identNumber']['manufacturerCode']:''?></span></p>
                </td>
            </tr>
        </table>
    </div>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Consumable</span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-indent:14pt; widows:0">
        <span style="font-family:Arial; font-size:14pt">None</span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Spare parts</span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-indent:14pt; widows:0">
        <span style="font-family:Arial; font-size:14pt">None</span></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; text-decoration:underline">Safety</span></p>

    <?php if($warningCaution && is_array($warningCaution)):?>
        <?php foreach($warningCaution as $v):?>
            <p style="font-size:18pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
                <span style="color:red; font-family:Arial; font-size:18pt; text-decoration:underline">WARNING:</span>

                <span style="color:red; font-family:Arial; font-size:18pt"><?=$v['warningAndCautionPara']?></span>

            </p>
        <?php endforeach ;?>
    <?php else:?>
        <?php if($warningCaution):?>
        <p style="font-size:18pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
            <span style="color:red; font-family:Arial; font-size:18pt; text-decoration:underline">WARNING:</span>

            <span style="color:red; font-family:Arial; font-size:18pt"><?=$warningCaution?$warningCaution['warningAndCautionPara']:''?></span>

        </p>
        <?php endif ;?>
    <?php endif ;?>

    <?php if($safetyCaution && is_array($safetyCaution)):?>
        <?php foreach($safetyCaution as $v):?>
        <p style="font-size:18pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
            <span style="color:#ffc000; font-family:Arial; font-size:18pt; text-decoration:underline">CAUTION:</span>

            <span style="color:#ffc000; font-family:Arial; font-size:18pt"><?=isset($v['warningAndCautionPara'])?$v['warningAndCautionPara']:$v?></span>

        </p>
        <?php endforeach ;?>
    <?php else:?>
        <?php if($safetyCaution):?>
        <p style="font-size:18pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
            <span style="color:#ffc000; font-family:Arial; font-size:18pt; text-decoration:underline">CAUTION:</span>

            <span style="color:#ffc000; font-family:Arial; font-size:18pt"><?=$safetyCaution?$safetyCaution['warningAndCautionPara']:''?></span>

        </p>
        <?php endif ;?>
    <?php endif ;?>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; font-weight:bold">Procedures</span></p>
    <ol type="1" style="margin:0pt; padding-left:0pt">
        <?php if($proceduralStep):?>
        <?php foreach($proceduralStep as $v):?>
        <li style="font-family:Arial; font-size:14pt; line-height:115%; margin:0pt 0pt 10pt 16.93pt; orphans:0; padding-left:4.32pt; text-indent:0pt; widows:0">
            <span style="font-family:Arial; font-size:14pt"><?=$v['para']?></span></li>
        <?php endforeach ;?>
        <?php endif ;?>
    </ol>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; widows:0">
        <span style="font-family:Arial; font-size:14pt; font-weight:bold">Close Requirement</span></p>
    <ol type="1" style="margin:0pt; padding-left:0pt">
        <?php if($closeReqCondGroup):?>
        <?php foreach($closeReqCondGroup as $k=>$v):?>
                <li style="font-family:Arial; font-size:14pt; line-height:115%; margin:0pt 0pt 10pt 16.68pt; orphans:0; padding-left:4.57pt; text-indent:0pt; widows:0">
                    <span style="font-family:Arial; font-size:14pt"><?=$v['reqCond']?></span></li>

                <?php if($k==0 && $closeReqRef_str):?>
                    <li style="font-family:Arial; font-size:14pt; line-height:115%; margin:0pt 0pt 10pt 16.68pt; orphans:0; padding-left:4.57pt; text-indent:0pt; widows:0">
                        <span style="font-family:Arial; font-size:14pt"><?=$closeReqRef_str?></span>
                        <?php if($closeReqRef_model):?>
                            <a href="<?=$closeReqRef_url?>"><span  style="color:#0070c0; font-family:Arial; font-size:14pt; text-decoration:none">Refer to <?=$closeReqRef_model?></span>
                            </a>
                        <?php endif ;?></li>
                <?php endif ;?>

            <?php endforeach;?>
        <?php endif ;?>
        </ol>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
        <img src="<?=$figureUrl?>" width="538" height="408" alt="PSCU AMM CABIN" style="-aw-left-pos:0pt; -aw-rel-hpos:column; -aw-rel-vpos:paragraph; -aw-top-pos:0pt; -aw-wrap-type:inline" /></p>
    <p style="font-size:14pt; line-height:115%; margin:0pt 0pt 10pt; orphans:0; text-align:center; widows:0">
        <span style="font-family:Arial; font-size:14pt"><?=$figureTitle?></span></p>
</div>

