
CREATE VIEW [dbo].[vWebManagementLeakLogForm]
AS

SELECT
[aai].[AssetAddressIndicationUID] AS [AssetAddressIndicationUID],
u.UserLANID AS [UserLANID],  -- TODO
[aai].[FoundDateTime] AS [Date],
[mg].FuncLocMap + '/' + [mg].FuncLocPlat AS [MapPlat],
ir.surveytype,
--[aai].[SurveyType],
CASE WHEN [aai].[StatusType] NOT IN ('In Progress', 'NotApproved', 'Rejected', 'Pending') THEN 1 ELSE 0 END [Approved],
[aai].[PipelineType],
[aa].[HouseNo],
[aa].[Street1] AS [Street],
[aa].[AptSuite],
[aa].[City],
[aa].[AssetIDNo] AS [MeterID],
[aa].[Comments],
[aai].[MapPlatLeakNumber] AS [MapPLatLeakNo],
CASE 
	WHEN [aai].[MapPlatLeakNumber] IS NOT NULL 
		THEN CAST([aai].[MapPlatLeakNumber] AS varchar(10)) + '/' 
		ELSE '' 
END + ISNULL([aai].[LeakNo], '') 
AS [LeakNo],
[aai].[Route] AS RouteName,
[aai].[FacilityType],
[aai].[AboveBelowGroundType] AS [AboveOrBelow],
[aai].[InitialLeakSourceType] AS [InitialLeakSource],
[aai].[ReportedByType] AS [ReportedBy],
[aai].[DescriptionReadingLocation] AS [DescriptionReadLoc],
[aai].[PavedType] AS [PavedWallToWall],
[aai].[SORLType] AS [SurfaceOverReadLoc],
[aai].[SORLOther] AS [OtherLocationSurface],
[aai].[Within5FeetOfBuildingType] AS [Within5FeetOfBuilding],
[aai].[SuspectedCopperType] AS [SuspectCopper],
[aai].[FoundBy] AS [InstFoundBy],
[aai].[InstrumentTypeGradeByType] AS [GradeByInstType],
[aai].[GradeBy] AS [InstGradeBy],
[aai].[ReadingGrade] AS [ReadingInPercentGas],
[aai].[GradeType] AS [Grade],
[aai].[InfoCodesType] AS [InfoCodes],
CASE 
	WHEN [aai].[PotentialHCAType] = 'Y' 
		THEN 'Yes' 
		ELSE 'No' 
END AS [PotentialHCA],
[aai].[HCAConstructionSupervisorUserUID] AS [ConstructionSupervisor],
[aai].[HCADistributionPlanningEngineerUserUID] AS [DistPlanningEngineer],
[aai].[HCAPipelineEngineerUserUID] AS [PipelineEngineer],
ISNULL([aai].[Comments], '') AS [LocationRemarks],
[aai].[LockedFlag] AS [LockFlag]
FROM (SELECT * FROM [tgAssetAddressIndication] WHERE [ActiveFlag] = 1) aai
Left JOIN (SELECT * FROM [tgAssetAddress] WHERE [ActiveFlag] = 1) aa ON [aai].[AssetAddressUID] = [aa].[AssetAddressUID]
Left JOIN (SELECT * FROM [dbo].[rgMapGridLog] WHERE [ActiveFlag] = 1) mg ON [mg].[MapGridUID] = [aai].[MapGridUID]
Left Join (Select * from [dbo].[UserTb] where UserActiveFlag = 1) u on aai.CreatedUserUID = u.UserUID
left join (select * from [dbo].[tgAssetInspection] where ActiveFlag = 1) [ai] on [ai].AssetInspectionUID = aa.AssetInspectionUID
Left Join (Select * From [dbo].[tInspectionRequest] where ActiveFlag = 1) ir on ir.InspectionRequestUID = [ai].InspectionRequestUID
