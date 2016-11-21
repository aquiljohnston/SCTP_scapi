
CREATE View [dbo].[vINF004] AS

SELECT
 msp.InspectionRequestUID [MapStampUID]
,msp.MapStampPicaroUID [TaskOutUID]
,'All' AS [MapAreNumber] 
,ir.LsNtfNo
,CASE WHEN ir.StatusType = 'Submit/Pending' THEN 'Y' ELSE 'N' END [SurveyCompleteFlag]

,CAST(svor.UserLANID AS VARCHAR(4)) AS [SRVYLANID]

,FORMAT(ir.SubmittedDTLT, 'MMddyyyy') [SurveySubmissionDate]
,'I' [SurveyType]
,FORMAT(msp.CreatedDateTime, 'MMddyyyy') [SurveyDate]
,CAST(sup.UserLANID AS VARCHAR(4))	AS [SPVRLANID]

,'Picarro' [EQOBJTYPE]
,msp.PicaroEquipmentID [EQSERNO]
,COALESCE(CAST(msp.WindSpeedStart AS VARCHAR(20)), 'NA') [WindSpeedStart] 
,COALESCE(CAST(msp.WindSpeedMid AS VARCHAR(20)), 'NA') [WindSpeedMid]
,'M' [SurveyMode]
,msp.Hours [EnteredHours]
,msp.FeetOfMain [EnteredFeet]
,msp.Services [EnteredServices]
,CASE WHEN ir.ReturnedFlag = 1 THEN 'Y' ELSE 'N' END [ResponseFlag]

-- SELECT * 
-- SELECT Count(*) 
FROM
	[tMapStampPicaro] msp

INNER JOIN [tInspectionRequest] ir
	ON  ir.InspectionRequestUID = msp.InspectionRequestUID
	AND ir.ActiveFlag = 1 
	AND ir.StatusType <> 'Completed'
	
LEFT  JOIN [UserTb] svor 
	ON	svor.UserUID = msp.ModifiedByUserUID
	AND svor.UserActiveFlag = 1

LEFT  JOIN [rWorkCenter] wc
	ON wc.WorkCenterAbbreviationFLOC = ir.MWC

LEFT  JOIN [UserTb] sup 
	ON	sup.UserUID = wc.SupervisorUID
	AND sup.UserActiveFlag = 1

WHERE 
ISNULL(msp.PicaroEquipmentID, '') <> ''
AND msp.ActiveFlag = 1

UNION 

SELECT 
 isr.InspectionRequestUID AS [MapStampUID]
,isr.TaskOutUID
,CAST(ISNULL(isr.MapAreaNumber, 0) AS VARCHAR(5)) AS [MapAreNumber]
,ir.LsNtfNo
,CASE WHEN ir.StatusType = 'Submit/Pending' THEN 'Y' ELSE 'N' END AS [SurveyCompleteFlag] -- Need to correct. What is the status when the INF004 is submitted from MapStamp Screen? Does the SP run first to set the status? or do we open up the staus up to more values?
,CAST(svor.UserLANID AS VARCHAR(4)) AS [SRVYLANID]

,FORMAT(ir.SubmittedDTLT, 'MMddyyyy') AS [SurveySubmissionDate]
, CASE 
	WHEN CHARINDEX('TR', isr.InspectionServicesUID) > 0 THEN 'T'
	WHEN CHARINDEX('FOV', isr.InspectionServicesUID) > 0 THEN 'V'
	WHEN CHARINDEX('LISA', isr.InspectionServicesUID) > 0 THEN 'L'
	WHEN CHARINDEX('GAP', isr.InspectionServicesUID) > 0 THEN 'G' -- Fixed
	WHEN CHARINDEX('PIC', isr.InspectionServicesUID) > 0 THEN 'I' -- NEW
 END AS [SurveyType]
,FORMAT(isr.SrcDTLT, 'MMddyyyy') AS [SurveyDate]

,CAST(sup.UserLANID AS VARCHAR(4))	AS [SPVRLANID]

,isr.EquipmentType AS [EQOBJTYPE]
,isr.SerialNumber AS [EQSERNO]
,COALESCE(CAST(wss.WindSpeed AS VARCHAR(20)), 'NA') AS [WindSpeedStart]
,COALESCE(CAST(wsm.WindSpeed AS VARCHAR(20)), 'NA') AS [WindSpeedMid]
,isr.SurveyMode
,isr.EstimatedHours AS [EnteredHours]
,isr.EstimatedFeet AS[EnteredFeet]
,isr.EstimatedServices AS [EnteredServices]
,CASE WHEN ir.ReturnedFlag = 1 THEN 'Y' ELSE 'N' END AS [ResponseFlag]

-- SELECT *
-- SELECT Count(*) 
FROM 
	[tInspectionService] isr

INNER JOIN [tInspectionRequest] ir
	ON ir.InspectionRequestUID = isr.InspectionRequestUID
	AND ir.ActiveFlag = 1 
	AND ir.StatusType <> 'Completed'

LEFT JOIN [tgWindSpeed] wss
	ON  wss.WindSpeedUID = isr.WindSpeedStartUID
	AND wss.ActiveFlag = 1

LEFT  JOIN [tgWindSpeed] wsm
	ON  wsm.WindSpeedUID = isr.WindSpeedMidUID
	AND wsm.ActiveFlag = 1

LEFT  JOIN [UserTb] svor 
	ON	svor.UserUID = isr.CreatedUserUID
	AND svor.UserActiveFlag = 1

LEFT  JOIN [rWorkCenter] wc
	ON wc.WorkCenterAbbreviationFLOC = ir.MWC

LEFT  JOIN [UserTb] sup 
	ON	sup.UserUID = wc.SupervisorUID
	AND sup.UserActiveFlag = 1

WHERE 
	isr.ActiveFlag = 1
AND CHARINDEX('Forms/', isr.MasterLeaklogUID) = 0 
AND isr.SurveyMode <> 'G' 
AND isr.PlaceHolderFlag = 0
-------------------------------------------------
-- Debug while in Test Mode
-- Bad data is causing major issues
-------------------------------------------------
AND svor.UserLANID IS NOT NULL
AND isr.SrcDTLT IS NOT NULL