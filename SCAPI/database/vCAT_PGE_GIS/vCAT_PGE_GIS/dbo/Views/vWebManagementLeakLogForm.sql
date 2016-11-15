



CREATE VIEW [dbo].[vWebManagementLeakLogForm]
AS

SELECT
[aai].[AssetAddressIndicationUID] AS [AssetAddressIndicationUID],
CreatedUser.UserLANID AS [CreatorLANID],  -- TODO
[aai].[FoundDateTime] AS [CreatedDate],
[mg].FuncLocMap + '/' + [mg].FuncLocPlat AS [MapPlat],
ir.surveytype [SurveyType],
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
LTRIM(RTRIM(ISNULL(Foundby.EquipmentType, 'V - Visual'))) AS [InstFoundBy],
Left(LTRIM(RTRIM(ISNULL(Gradeby.EquipmentType, 'V - Visual'))), 1) AS [GradeByInstType],
LTRIM(RTRIM(ISNULL(Gradeby.EquipmentType, 'V - Visual')))  AS [InstGradeBy],
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
ISNULL(ApprovedUser.UserLANID, '') AS [ApproverLANID],
aai.ApprovedDTLT [ApprovedDate],
aai.Photo1,
aai.Photo2,
aai.Photo3,
CASE WHEN aai.Latitude <> 0 THEN 1 ELSE 0 END [leakGPSIcon],
CASE WHEN aa.Latitude <> 0 THEN 1 ELSE 0 END [addressGPSIcon],
[aai].[LockedFlag] AS [LockFlag]
FROM (SELECT * FROM [tgAssetAddressIndication] WHERE [ActiveFlag] = 1) aai
Left JOIN (SELECT * FROM [tgAssetAddress] WHERE [ActiveFlag] = 1) aa ON [aai].[AssetAddressUID] = [aa].[AssetAddressUID]
Left JOIN (SELECT * FROM [dbo].[rgMapGridLog] WHERE [ActiveFlag] = 1) mg ON [mg].[MapGridUID] = [aai].[MapGridUID]
Left Join (Select * from [dbo].[UserTb] where UserActiveFlag = 1) CreatedUser on aai.CreatedUserUID = CreatedUser.UserUID
Left Join (Select * from [dbo].[UserTb] where UserActiveFlag = 1) ApprovedUser on aai.ApprovedByUserUID = ApprovedUser.UserUID
left join (select * from [dbo].[tgAssetInspection] where ActiveFlag = 1) [ai] on [ai].AssetInspectionUID = aa.AssetInspectionUID
Left Join (Select * From [dbo].[tInspectionRequest] where ActiveFlag = 1) ir on ir.InspectionRequestUID = [ai].InspectionRequestUID
Left Join (Select * from [dbo].[tInspectionsEquipment] where ActiveFlag = 1) FoundBy on FoundBy.InspecitonEquipmentUID = aai.EquipmentFoundByUID
Left Join (Select * from [dbo].[tInspectionsEquipment] where ActiveFlag = 1) GradeBy on GradeBy.InspecitonEquipmentUID = aai.EquipmentGradeByUID
