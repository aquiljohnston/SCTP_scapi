



CREATE VIEW [dbo].[vWebManagementTrackerMapGridCompliance] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementTrackerMapGridCompliance]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerMapGridCompliance] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerMapGridCompliance] TO [ApplicationCometTracker] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerMapGridCompliance] TO [ApplicationLH] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-11-25      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vWebManagementTrackerMapGridCompliance]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------
---- 

SELECT 
--ir.ActiveFlag,
--ir.CreateDTLT,
--ir.CreatedUserUID, 
 ir.LsNtfNo
,ir.OrderNo
,ir.StatusType
--,ir.MWC			-- Debug Only
--,ir.FLOC			-- Debug Only
,mg.FuncLocMWC AS [MWC]
,mg.FLOC	   AS [FLOC]
,UPPER(ir.InspectionFrequencyType) AS [SurveyFreq]
,LEFT(mg.FLOC, 2) AS PipelineType
,REPLACE(RIGHT(mg.FLOC, 9), '.', '-') AS [MapID]
,mg.FuncLocMap		AS [Wall]
,mg.FuncLocPlat		AS [Plat]
,DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) AS DaysUntilDueDate
				,CASE 
					WHEN ir.StatusType = 'Completed'												THEN 5  -- Green (Completed)
					ELSE 
						CASE
							
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) < 1					THEN 0	-- Black
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) BETWEEN 1 AND 30	THEN 1	-- Red
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) BETWEEN 31 AND 60	THEN 2	-- Orange
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) BETWEEN 61 AND 100	THEN 3	-- Yellow
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) > 100  				THEN 4	-- White
							ELSE																		 5  -- White
						END
				 END AS MapGridComplianceCodeCode
				,CASE 
					WHEN ir.StatusType = 'Completed'												THEN 'GREEN - Completed'  -- Green (Completed)
					ELSE 
						CASE
						
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) < 1					THEN 'Out of Complinace'
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) BETWEEN 1 AND 30	THEN 'Between 1 and 30 days'
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) BETWEEN 31 AND 60	THEN 'Between 31 and 60 days'
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) BETWEEN 61 AND 100	THEN 'Between 61 and 100 days'
							WHEN DATEDIFF(DAY, GETDATE(), ir.ComplianceDueDate) > 100  				THEN 'Greater than 101 days'
							ELSE																		 'Not Specified' 
						END
				 END AS MapGridComplianceDescription

,ir.ComplianceDueDate
,ir.ScheduledStartDate
,ir.ScheduledCompleteDate
,ir.ReleaseDate
,ir.PrevServ
,ir.PrevFtOfMain
,CASE WHEN ir.ReturnFlag = 1 THEN 'Yes' ELSE NULL END ReturnFlag
,ir.ReturnComments
,mg.Shape

-- SELECT DISTINCT FLOC --  547
-- SELECT * -- 567 (20 dup FLOC via SurveyFreq
FROM 
	(
		SELECT * 
		FROM [dbo].[tInspectionRequest] ir -- INF002 data
		WHERE ir.ActiveFlag = 1
		AND ir.LsNtfNo IS NOT NULL
	) ir
RIGHT JOIN [dbo].[rgMapGridLog] mg
	ON mg.MapGridUID = ir.MapGridUID
	AND	mg.ActiveFlag = 1