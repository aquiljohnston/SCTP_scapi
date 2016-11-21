


CREATE VIEW [dbo].[vINF006] AS 

SELECT 

---- Debub ------------------------------------------------------

-- mll.StatusType AS Debug_StatusType,											
-- aadd.City AS Debug_City,
-- '|---------------------| ' AS INF006_Break,
---- Debub ------------------------------------------------------
 CAST(ind.AssetAddressIndicationUID AS VARCHAR(100))			AS [IND_UID] 
,CAST(ind.MapPlatLeakNumber AS VARCHAR(6))						AS [MAP_PLAT_LEAK_NO]	
,CAST(ind.LeakNo AS VARCHAR(16))								AS [LEAK_NO]			-- This cuts off LanIDs > 4 characters. This format may be an issue if 2016 (YYYY) is displayed vs 16 (YY)

,FORMAT(ind.FoundDateTime, 'MMddyyyy')							AS [RPT_DATE]
,FORMAT(ind.FoundDateTime, 'HHmmss')							AS [RPT_TIME]

,FORMAT(ind.FoundDateTime, 'MMddyyyy')							AS [READ_DATE]
,FORMAT(ind.FoundDateTime, 'HHmmss')							AS [READ_TIME]
--,ind.CreatedUserUID AS UserLanID								-- Debub ------------------------------------------------------
,CAST(svor.UserLANID AS VARCHAR(4))								AS [READ_LANID]			
,FORMAT(ind.ApprovedDTLT, 'MMddyyyy')							AS [SPVR_APPROVAL_DATE]	

--,ind.ApprovedByUserUID AS SupervisorLanID						-- Debub ------------------------------------------------------
,CAST(sup.UserLANID AS VARCHAR(4))								AS [SPVR_LANID]			

,CAST(mg.FLOC AS VARCHAR(30))									AS [FLOC]				
,CAST(wc.WorkCenterAbbreviation AS VARCHAR(8))					AS [MWC]				
--,CAST(wc.Division AS VARCHAR(10))								AS [DIV]				-- Convert this to the Division Code 2016-11-10
,CAST(wc.DivisionCode AS VARCHAR(10))							AS [DIV]				-- New Added 2016-11-10

,CASE 
	WHEN SUBSTRING(mg.FLOC,2,1) = 'T' THEN 'T' 
	ELSE 'D'
 END															AS [PIPE_TYPE]			-- ETL (D, T) -- Does everything get D unless its GT.PHSY.TRNS.9999.0T99

,CAST(ind.Route AS VARCHAR(12))									AS [ROUTE_NO]			-- If the Pipeline Line Time = 'GT/LT' only AND FLOC <> 'GT.PHYS.TRNS.9999.0T99'
,CAST(ind.Latitude AS FLOAT)									AS [LAT]				
,CAST(ind.Longitude AS FLOAT)									AS [LONG]				
,CAST(ind.HDOP AS FLOAT)										AS [ACCURACY_FT]		-- DECIMAL(4,2)
,CAST(aadd.HouseNo AS VARCHAR(10))								AS [HOUSE_NO]
,CAST(aadd.Street1 AS VARCHAR(60))								AS [STREET]
,CAST(aadd.AptSuite AS VARCHAR(20))								AS [APT]
,CAST(aadd.City AS VARCHAR(40))									AS [CITY]				-- Needs a VR for City Table
,CAST(cc.CountyCode AS VARCHAR(3))								AS [COUNTY]				-- Lookup convertions to County Code from City Table / County Lookup

,CAST(ind.FacilityType AS CHAR(1))								AS [FAC_TYPE]				

,CAST(ind.AboveBelowGroundType AS CHAR(1))						AS [ABV_BLW]
,CAST(ddils.OutValue AS VARCHAR(2))								AS [INT_LK_SRC]
,CAST(ind.ReportedByType AS CHAR(1))							AS [RPT_BY]				
,CAST(ind.DescriptionReadingLocation AS VARCHAR(500))			AS [DESC_READ_LOC]
,CAST(ind.PavedType AS CHAR(1))									AS [WL_2_WL]			-- Y/N/NULL
,CAST(ind.SORLType AS CHAR(1))									AS [SORL]				
,CAST(ind.SORLOther AS VARCHAR(32))								AS [SORL_OTHER]
,CAST(ind.Within5FeetOfBuildingType AS CHAR(1))					AS [WN_5FT]				-- Y/N/NULL
,CAST(ind.SuspectedCopperType AS CHAR(1))						AS [SUSP_COP]			-- Y/N/NULL

,LEFT(LTRIM(ind.InstrumentTypeGradeByType), 1)					AS [INST_TYPE_CODE]
,CAST(ie.SerialNumber AS VARCHAR(25))							AS [GRD_SERAL_NO]		
,CAST(ind.ReadingGrade AS DECIMAL(8,3))							AS [PCT_GAS]
,CAST(ind.GradeType AS VARCHAR(2))								AS [GRADE]
,CAST(NULLIF(ind.InfocodesType,'Please Make Selection') AS CHAR(1))	AS [INFO_CODE] -- no FieldDisplay Please Make Selection

,CAST(ind.PotentialHCAType AS CHAR(1))							AS [HCA]				-- Y/N/NULL
,CAST(ind.Comments AS VARCHAR(100))								AS LOC_REMRK

,CAST(ind.StationBegin AS VARCHAR(18))							AS [STA_BEGIN]			-- If the Pipeline Line Time = 'GT/LT' only AND FLOC <> 'GT.PHYS.TRNS.9999.0T99' 
,CAST(ind.StationEnd AS VARCHAR(18))							AS [STA_END]			-- If the Pipeline Line Time = 'GT/LT' only AND FLOC <> 'GT.PHYS.TRNS.9999.0T99' 

,CAST(ind.HCAConstructionSupervisorUserUID AS VARCHAR(4))		AS [HCA_CONST_ID]
,CAST(ind.HCADistributionPlanningEngineerUserUID AS VARCHAR(4))	AS [HCA_DISTR_ID]
,CAST(ind.HCAPipelineEngineerUserUID AS VARCHAR(4))				AS [HCA_PIPEL_ID]

,CAST(ind.Photo1 AS VARCHAR(150))								AS [IND_PHOTO1]
,CAST(ind.Photo2 AS VARCHAR(150))								AS [IND_PHOTO2]
,CAST(ind.Photo3 AS VARCHAR(150))								AS [IND_PHOTO3]

,ind.MasterLeakLogUID

-- SELECT TOP 100 * 
-- SELECT * 
-- [vCAT_PGE_GIS_DEV].
-- [vCAT_PGE_GIS_STAGE].

FROM	[dbo].[tgAssetAddressIndication] ind

LEFT  JOIN  [dbo].[tgAssetAddress] aadd
	ON	aadd.AssetAddressUID = ind.AssetAddressUID

LEFT  JOIN	[dbo].[rgMapGridLog] mg
	ON	mg.MapGridUID = ind.MapGridUID
	AND mg.ActiveFlag = 1

LEFT  JOIN	[dbo].[tMasterLeakLog] mll				
	ON	mll.MasterLeakLogUID = ind.MasterLeakLogUID
	AND mll.ActiveFlag = 1

LEFT  JOIN [dbo].[UserTb] svor 
	ON	svor.UserUID = ind.CreatedUserUID
	AND svor.UserActiveFlag = 1

LEFT  JOIN [dbo].[rWorkCenter] wc
	ON wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC

LEFT  JOIN [dbo].[rCityCounty] cc
	ON cc.City = aadd.City

LEFT  JOIN [dbo].[UserTb] sup 
	ON	sup.UserUID = ind.ApprovedByUserUID
	AND sup.UserActiveFlag = 1

LEFT  JOIN ( SELECT FieldDisplay, FieldDescription, OutValue
			 FROM [dbo].[rDropDown] dd
			 WHERE FilterName = 'ddInitialLeakSourceType' AND SortSeq <> 0
			) ddils ON ddils.FieldDisplay = ind.InitialLeakSourceType

LEFT  JOIN [dbo].[tInspectionsEquipment] ie
	ON ie.InspecitonEquipmentUID = ind.EquipmentGradeByUID
	AND ie.InspecitonEquipmentUID <> '' -- Extra code to eleminate bad data
	AND ie.ActiveFlag = 1

WHERE 
ind.ActiveFlag = 1
AND ind.FoundDateTime > '2016-11-03 12:00' -- Extra Code to eleminate bad data
and IND.StatusType <> 'Completed'
AND mll.StatusType = 'Submit/Pending'