
CREATE PROCEDURE [dbo].[udsp_rptTest_v01]
(
	 @ReportID			INT			= 1
	,@UserLoginID		VARCHAR(20) = NULL
	,@StartDate			DATETIME	= NULL
	,@EndDate			DATETIME	= NULL
) AS

/*****************************************************************************************************************
NAME:		udsp_rptTest_v01
SERVER:     SC1-SQL01
DATABASE:   vCAT_PGE_GIS_DEV
    
Report Requirements:             - 
                                 - 

Outstanding Issues:              - None at this time.

Permissions for SP:				 - GRANT EXECUTE ON [dbo].[udsp_rptTest_v01] TO [Reports] AS [dbo] -- Need to build this up

HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-10-31		CBowker			  Initial Build with Parameters


Test Data
***********************************************************************************************************/
--DECLARE
--	 @ReportID			INT				=	1
--	,@UserLoginID		VARCHAR(20)		=	'< All >' --'TQuin - QUINN, THOMAS' -- NULL
--	,@StartDate			DATETIME		=	'01/01/2016'
--	,@EndDate			DATETIME		=	'01/05/2016'

/*****************************************************************************************************************
	-- EXECUTE  dbo.udsp_rptTest_v01 @ReportID, @StartDate, @EndDate, @UserLoginID
	-- EXECUTE  dbo.udsp_rptTest_v01 
	-- EXECUTE  dbo.udsp_rptTest_v01 1, NULL, NULL, NULL
	-- EXECUTE  dbo.udsp_rptTest_v01 1, NULL, '10/25/2016', '10/30/2016'
	-- EXECUTE  dbo.udsp_rptTest_v01 1, NULL, NULL, '10/25/2015'
	-- EXECUTE  dbo.udsp_rptTest_v01 1, NULL, '10/05/2016', NULL
	-- EXECUTE  dbo.udsp_rptTest_v01 1, NULL, '10/05/2016'

	-- EXECUTE  dbo.udsp_rptTest_v01 1, 'mdavis - DAVIS, MICHAEL', '10/05/2016', '10/30/2016'
	-- EXECUTE  dbo.udsp_rptTest_v01 1, 'mdavis - DAVIS, MICHAEL', NULL, '10/30/2016'
	-- EXECUTE  dbo.udsp_rptTest_v01 1, 'mdavis - DAVIS, MICHAEL', '10/05/2016', NULL
	-- EXECUTE  dbo.udsp_rptTest_v01 1, 'mdavis - DAVIS, MICHAEL', NULL, NULL


******************************************************************************************************************/

SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED 
SET NOCOUNT ON -- Must have when calling from MS Excel to only limit one result set
--SET NOCOUNT OFF -- Must have when calling from MS Excel to only limit one result set


---- Cleanup ----------------------------------------------------------------------------------------------------------
IF OBJECT_ID('tempdb..#DD', 'U') IS NOT NULL
    DROP TABLE #DD

---- Set Overall Defaults for the Report ------------------------------------------------------------------------------
SELECT
 @ReportID		= COALESCE(@ReportID, 1)
,@StartDate		= CASE
					WHEN @StartDate IS NULL AND @EndDate IS NOT NULL THEN COALESCE(@EndDate, GETDATE())
					ELSE COALESCE(@StartDate, GETDATE())
				  END
,@EndDate		= CASE 
					WHEN @EndDate IS NULL AND @StartDate IS NOT NULL THEN COALESCE(@StartDate, GETDATE())
					ELSE COALESCE(@EndDate, GETDATE())
				  END
,@UserLoginID	= CASE
					WHEN @UserLoginID IS NULL THEN NULL
					WHEN @UserLoginID = '< All >' THEN '< All >'
					ELSE RTRIM(LEFT(@UserLoginID,CHARINDEX(' -',@UserLoginID,1)))
				  END

--SELECT @StartDate, @EndDate, @UserLoginID

---- Declare Overall variables for the Report -------------------------------------------------------------------------
DECLARE		 
	 @Date			DATE		= GETDATE() -- '2015-01-01'
	,@PriorDay		DATETIME	= DATEADD(s,-1,DATEADD(DAY, DATEDIFF(DAY, 0, GETDATE()), 0))
	,@Prior30		DATETIME	= DATEADD(dd,-31,DATEADD(s,-1,DATEADD(DAY, DATEDIFF(DAY, 0, GETDATE()), 0)))

	
SET @StartDate					= DATEADD(DAY,0,DATEADD(DAY, DATEDIFF(DAY, 0, @StartDate), 0))					-- Start of day (Good)
SET @EndDate					= DATEADD(s,-1,DATEADD(DAY, DATEDIFF(DAY, 0, @EndDate), 1))						-- End of day

--SELECT @StartDate, @EndDate, @UserLoginID


---- Test for UserLoginID Filter for udsp from [dbo].[r_Reports].[ParmInspector] --------------------------------------

IF @UserLoginID IS NULL 
	BEGIN


--DECLARE
--	 @ReportID			INT				=	1
--	,@UserLoginID		VARCHAR(20)		=	'< All >' --'TQuin - QUINN, THOMAS' -- NULL
--	,@StartDate			DATETIME		=	'10/06/2016'
--	,@EndDate			DATETIME		=	'10/25/2016'


------ Cleanup ----------------------------------------------------------------------------------------------------------
--IF OBJECT_ID('tempdb..#DD', 'U') IS NOT NULL
--    DROP TABLE #DD


		SELECT  -- 20160111 Added
		'< All >' AS Drop_Down
		,NULL AS LastName
		,NULL AS FirstName
		INTO #DD
		
		UNION ALL
		
		SELECT DISTINCT
		 CONCAT(RTRIM(u.UserLANID),' - ', UPPER(RTRIM(u.UserLastName)),', ',UPPER(RTRIM(u.UserFirstName))) AS Drop_Down
		,u.UserLastName
		,u.UserFirstName
		FROM 
			[dbo].[tgAssetAddress] aa 
		LEFT  JOIN [dbo].[UserTb] u
			ON u.UserUID = aa.CreatedUserUID
			AND u.UserActiveFlag = 1
		WHERE 
			aa.ActiveFlag = 1
		AND CAST(aa.SrcDTLT AS DATE) BETWEEN @StartDate AND @EndDate
		ORDER BY
		2,3 -- 20160111 Added -- Cannot be specific
	
		SELECT Drop_Down FROM #DD -- DROP TABLE #DD
	
	END

ELSE

---- Build Report Master File based upon Overall Start and End Dates --------------------------------------------------

	BEGIN



--DECLARE
--	 @ReportID			INT				=	1
--	,@UserLoginID		VARCHAR(20)		=	'< All >' --'mdavis - DAVIS, MICHAEL' -- NULL
--	,@StartDate			DATETIME		=	'10/25/2016'
--	,@EndDate			DATETIME		=	'10/30/2016'


		SELECT 
			 GETDATE() AS [Report Date]
			,'Report Test 1' AS [Report Title]
			,u.UserLANID
			,u.UserLastName
			,u.UserFirstName
			,u.UserName
			,ir.SurveyType
			,ir.MWC
			,wc.WorkCenter AS [Work Center]
			,wc.Division
			,wc.Region
			,ir.ComplianceDueDate
			,CAST(aa.SrcDTLT AS DATE) AS SurveyDate
			,aa.SrcDTLT AS SurveyDateTime
		FROM 
			[dbo].[tInspectionRequest] ir 

		INNER JOIN [dbo].[tgAssetAddress] aa 
			ON	aa.InspectionRequestUID = ir.InspectionRequestUID
			AND	aa.ActiveFlag = 1

		LEFT JOIN [dbo].[tgAssetAddressIndication] ind
			ON	ind.InspectionRequestUID = ir.InspectionRequestUID
			AND	ind.ActiveFlag = 1

		LEFT  JOIN [dbo].[UserTb] u
			ON u.UserUID = aa.CreatedUserUID
			AND u.UserActiveFlag = 1

		LEFT  JOIN [dbo].[rWorkCenter] wc 
			ON	wc.WorkCenterAbbreviationFLOC = ir.MWC
			AND wc.ActiveFlag = 1

		WHERE
				u.UserLANID = CASE WHEN @UserLoginID = '< All >' THEN u.UserLANID  ELSE @UserLoginID END 
			AND CAST(aa.SrcDTLT AS DATE) BETWEEN @StartDate AND @EndDate

	END