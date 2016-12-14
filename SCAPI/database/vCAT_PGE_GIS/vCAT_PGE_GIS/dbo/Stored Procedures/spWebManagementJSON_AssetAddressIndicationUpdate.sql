




CREATE PROCEDURE [dbo].[spWebManagementJSON_AssetAddressIndicationUpdate]
(
      @JSON_Str VarChar(Max)
    
)
AS 

-- This First Part should be in all SP userd to process a JSON record.  
-- It is designed to archive the original data

	SET NOCOUNT ON
	
	Declare @SingleQuote char(1) = CHAR(39)
		

	--Set @SingleQuote = CHAR(39)

	--Set @TransactionType = 'InspectionServiceUpdate'

	
	
		Select * Into #JSON_Parse From [dbo].[parseJSON](@JSON_Str)
	
		Update #JSON_Parse Set StringValue = 0 where charindex('flag', name) > 0 and StringValue = 'null'
		Update #JSON_Parse Set StringValue = 0 where ValueType = 'boolean' and StringValue = 'false'
		Update #JSON_Parse Set StringValue = 1 where ValueType = 'boolean' and StringValue = 'true'
		Update #JSON_Parse Set StringValue = '' where StringValue = 'null' or StringValue = 'Please Make A Selection'
		Update #JSON_Parse Set StringValue = '19000101000000' Where Name In ('SrcDTLT', 'SrcDTGMT', 'SrcOpenDTLT', 'SrcClosedDTLT') and StringValue = ''
/*	
	
		Select @ClientID = 1
		--Select @ClientID = StringValue From #JSON_Parse Where Name = 'ClientID'
		Select Top 1 @UserUID = StringValue From #JSON_Parse Where Name = 'ActivityCreatedUserUID'

*/
		Insert Into [dbo].[tWEBDataInsertArchive]
		(
			TransactionType
			,InsertedData
			)
		Values (
			'AssetAddressIndicationUpdate'
			,@JSON_Str
			)



/***************************************************************************

--  Create the unique function needed for the record type below this line

***************************************************************************/

		Declare 

			@AssetAddressIndicationUID varchar(100)
			, @UserUID varchar(100)
			, @Approved varchar(20)
			, @HouseNo varchar(20)
			, @Street varchar(50)
			, @AptSuite varchar(50)
			, @City varchar(50)
			, @Comments varchar(2000)
			, @RouteName varchar(50)
			, @FacilityType varchar(200)
			, @InitialLeakSource varchar(200)
			, @ReportedBy varchar(100)
			, @DescriptionReadLoc varchar(200)
			, @PavedWallToWall varchar(200)
			, @SurfaceOverReadLoc varchar(200)
			, @OtherLocationSurface varchar(200)
			, @SuspectCopper varchar(200)
			, @InfoCodes varchar(200)
			, @PotentialHCA varchar(200)
			, @ConstructionSupervisorLANID varchar(20)
			, @DistPlanningEngineerLANID varchar(20)
			, @PipelineEngineerLANID varchar(20)
			, @ConstructionSupervisorUID varchar(100)
			, @DistPlanningEngineerUID varchar(100)
			, @PipelineEngineerUID varchar(100)
			, @LocationRemarks varchar(200)
			, @Datetime datetime
			, @Revision int
			, @AssetAddressUID varchar(200)
			, @ReturnVal Bit = 1
			, @MasterLeakLogUID varchar(100)
			, @PipelineType varchar(25)

		Select @Approved = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Approved'), '')
		Select @HouseNo = ISNULL((Select StringValue From #JSON_Parse Where Name = 'HouseNo'), '')
		Select @Street = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Street'), '')
		Select @AptSuite = ISNULL((Select StringValue From #JSON_Parse Where Name = 'AptSuite'), '')
		Select @City = ISNULL((Select StringValue From #JSON_Parse Where Name = 'City'), '')
		Select @Comments = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Comments'), '')
		Select @RouteName = ISNULL((Select StringValue From #JSON_Parse Where Name = 'RouteName'), '')
		Select @FacilityType = ISNULL((Select StringValue From #JSON_Parse Where Name = 'FacilityType'), '')
		Select @InitialLeakSource = ISNULL((Select StringValue From #JSON_Parse Where Name = 'InitialLeakSource'), '')
		Select @ReportedBy = ISNULL((Select StringValue From #JSON_Parse Where Name = 'ReportedBy'), '')
		Select @DescriptionReadLoc = ISNULL((Select StringValue From #JSON_Parse Where Name = 'DescriptionReadLoc'), '')
		Select @PavedWallToWall = ISNULL((Select StringValue From #JSON_Parse Where Name = 'PavedWallToWall'), '')
		Select @SurfaceOverReadLoc = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SurfaceOverReadLoc'), '')
		Select @OtherLocationSurface = ISNULL((Select StringValue From #JSON_Parse Where Name = 'OtherLocationSurface'), '')
		Select @SuspectCopper = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SuspectCopper'), '')
		Select @InfoCodes = ISNULL((Select StringValue From #JSON_Parse Where Name = 'InfoCodes'), '')
		Select @PotentialHCA = ISNULL((Select StringValue From #JSON_Parse Where Name = 'PotentialHCA'), '')
		Select @ConstructionSupervisorLANID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'ConstructionSupervisor'), '')
		Select @DistPlanningEngineerLANID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'DistPlanningEngineer'), '')
		Select @PipelineEngineerLANID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'PipelineEngineer'), '')
		Select @LocationRemarks = ISNULL((Select StringValue From #JSON_Parse Where Name = 'LocationRemarks'), '')
		Select @AssetAddressIndicationUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'AssetAddressIndicationUID'), '')
		Select @Datetime = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Date'), '')
		Select @UserUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'UserUID'), '')
		Select @PipelineType = ISNULL((Select StringValue From #JSON_Parse Where Name = 'PipelineType'), '')


		Select @ConstructionSupervisorUID = UserUID  from UserTb Where UserName = @ConstructionSupervisorLANID
		Select @DistPlanningEngineerUID = UserUID from UserTb Where UserName = @DistPlanningEngineerLANID
		Select @PipelineEngineerUID = UserUID from UserTb Where UserName = @PipelineEngineerLANID

		Set @PotentialHCA = CASE WHEN @PotentialHCA = 'Y' THEN 'YES' ELSE 'NO' END

Begin Try

	Begin Transaction

--Update Indication

	Update tgAssetAddressIndication set ActiveFlag = 0 where AssetAddressIndicationUID = @AssetAddressIndicationUID and ActiveFlag = 1

	Select @Revision = count(*) from tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID
	SELECT @MasterLeakLogUID = MasterLeakLogUID FROM tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID
	Insert Into tgAssetAddressIndication
	(
		AssetAddressIndicationUID,
		AssetAddressUID,
		InspectionRequestUID,
		MapGridUID,
		MasterLeakLogUID,
		ProjectID,
		SourceID,
		CreatedUserUID,
		ModifiedUserUID,
		SrcDTLT,
		
		GPSType,
		GPSSentence,
		Latitude,
		Longitude,
		SHAPE,
		Comments,
		RevisionComments,
		Revision,
		ActiveFlag,
		StatusType,
		ManualMapPlat,
		PipelineType,
		SurveyType,
		Map,
		Plat,
		RecordedMap,
		RecordedPlat,
		RecordedBlock,
		LandmarkType,
		Route,
		Line,
		HouseNoNAFlag,
		HouseNo,
		Street1,
		City,
		DescriptionReadingLocation,
		County,
		CountyCode,
		FacilityType,
		LocationType,
		InitialLeakSourceType,
		ReportedByType,
		LeakNo,
		SAPNo,
		PavedType,
		SORLType,
		SORLOther,
		Within5FeetOfBuildingType,
		SuspectedCopperType,
		EquipmentFoundByUID,
		FoundBy,
		FoundBySerialNumber,
		InstrumentTypeGradeByType,
		EquipmentGradeByUID,
		GradeBy,
		GradeBySerialNumber,
		ReadingGrade,
		GradeType,
		InfoCodesType,
		PotentialHCAType,
		Grade2PlusRequested,
		TwoPercentOrLessSuspectCopperFlag,
		LeakDownGradedFlag,
		HCAConstructionSupervisorUserUID,
		HCADistributionPlanningEngineerUserUID,
		HCAPipelineEngineerUserUID,
		Photo1,
		Photo2,
		Photo3,
		OptionalData1,
		OptionalData2,
		OptionalData3,
		OptionalData4,
		OptionalData5,
		OptionalData6,
		OptionalData7,
		OptionalData8,
		OptionalData9,
		OptionalData10,
		OptionalData11,
		OptionalData12,
		ApprovedFlag,
		ApprovedByUserUID,
		ApprovedDTLT,
		SubmittedFlag,
		SubmittedStatusType,
		SubmittedUserUID,
		SubmittedDTLT,
		ResponseStatusType,
		ResponseComments,
		ResponceErrorComments,
		ResponseDTLT,
		CompletedFlag,
		CompletedDTLT,
		AboveBelowGroundType,
		FoundDateTime,
		GPSSource,
		GPSTime,
		FixQuality,
		NumberOfSatellites,
		HDOP,
		AltitudemetersAboveMeanSeaLevel,
		HeightOfGeoid,
		TimeSecondsSinceLastDGPS,
		ChecksumData,
		Bearing,
		Speed,
		GPSStatus,
		NumberOfGPSAttempts,
		ActivityUID,
		AssetInspectionUID,
		MapPlatLeakNumber,
		LockedFlag,
		SAPComments,
		StationBegin,
		StationEnd
	)
	Select
		AssetAddressIndicationUID,
		AssetAddressUID,
		InspectionRequestUID,
		MapGridUID,
		MasterLeakLogUID,
		ProjectID,
		SourceID,
		CreatedUserUID,
		@UserUID, --ModifiedUserUID,
		@Datetime, --SrcDTLT,
		
		GPSType,
		GPSSentence,
		Latitude,
		Longitude,
		SHAPE,
		@LocationRemarks, --Comments,
		RevisionComments,
		@Revision, -- Revision,
		1, --ActiveFlag,
		StatusType , --StatusType,
		ManualMapPlat,
		@PipelineType, --PipelineType,
		SurveyType,
		Map,
		Plat,
		RecordedMap,
		RecordedPlat,
		RecordedBlock,
		LandmarkType,
		@RouteName, --Route,
		Line,
		HouseNoNAFlag,
		HouseNo,
		Street1,
		City,
		@DescriptionReadLoc, --DescriptionReadingLocation,
		County,
		CountyCode,
		@FacilityType, --FacilityType,
		LocationType,
		@InitialLeakSource, --InitialLeakSourceType,
		@ReportedBy, --ReportedByType,
		LeakNo,
		SAPNo,
		@PavedWallToWall, --PavedType,
		@SurfaceOverReadLoc, --SORLType,
		@OtherLocationSurface, --SORLOther,
		Within5FeetOfBuildingType,
		@SuspectCopper, --SuspectedCopperType,
		EquipmentFoundByUID,
		FoundBy,
		FoundBySerialNumber,
		InstrumentTypeGradeByType,
		EquipmentGradeByUID,
		GradeBy,
		GradeBySerialNumber,
		ReadingGrade,
		GradeType,
		@InfoCodes, --InfoCodesType,
		@PotentialHCA, --PotentialHCAType,
		Grade2PlusRequested,
		TwoPercentOrLessSuspectCopperFlag,
		LeakDownGradedFlag,
		@ConstructionSupervisorUID, --HCAConstructionSupervisorUserUID,
		@DistPlanningEngineerUID, --HCADistributionPlanningEngineerUserUID,
		@PipelineEngineerUID, --HCAPipelineEngineerUserUID,
		Photo1,
		Photo2,
		Photo3,
		OptionalData1,
		OptionalData2,
		OptionalData3,
		OptionalData4,
		OptionalData5,
		OptionalData6,
		OptionalData7,
		OptionalData8,
		OptionalData9,
		OptionalData10,
		OptionalData11,
		OptionalData12,
		1, --ApprovedFlag,
		@UserUID, --ApprovedByUserUID,
		@Datetime, --ApprovedDTLT,
		SubmittedFlag,
		SubmittedStatusType,
		SubmittedUserUID,
		SubmittedDTLT,
		ResponseStatusType,
		ResponseComments,
		ResponceErrorComments,
		ResponseDTLT,
		CompletedFlag,
		CompletedDTLT,
		AboveBelowGroundType,
		FoundDateTime,
		GPSSource,
		GPSTime,
		FixQuality,
		NumberOfSatellites,
		HDOP,
		AltitudemetersAboveMeanSeaLevel,
		HeightOfGeoid,
		TimeSecondsSinceLastDGPS,
		ChecksumData,
		Bearing,
		Speed,
		GPSStatus,
		NumberOfGPSAttempts,
		ActivityUID,
		AssetInspectionUID,
		MapPlatLeakNumber,
		LockedFlag,
		SAPComments,
		StationBegin,
		StationEnd
	from tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID and Revision = @Revision - 1


--Update AssetAddress

	Select @AssetAddressUID = AssetAddressUID from tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID and ActiveFlag = 1

	Update tgAssetAddress set ActiveFlag = 0 where AssetAddressUID = @AssetAddressUID and ActiveFlag = 1

	Select @Revision = count(*) from tgAssetAddress where AssetAddressUID = @AssetAddressUID

	Insert Into tgAssetAddress
	(
		AssetAddressUID,
		AssetUID,
		AssetInspectionUID,
		MasterLeakLogUID,
		MapGridUID,
		ProjectID,
		SourceID,
		CreatedUserUID,
		ModifiedUserUID,
		SrcDTLT,
		--SrvDTLT,
		--SrvDTLTOffset,
		GPSType,
		GPSSentence,
		Latitude,
		Longitude,
		SHAPE,
		Comments,
		RevisionComments,
		Revision,
		ActiveFlag,
		StatusType,
		NewAssetFlag,
		NonAssetLocationFlag,
		AssetAddessCorrectionFlag,
		AssetIDNumberCorrectionFlag,
		AssetConfirmFlag,
		RouteNo,
		RouteSeq,
		SortOrder,
		AssetAccountNo,
		AssetAccountName,
		AssetName,
		AssetLocationID,
		AssetLocationCode,
		AssetIDStatus,
		AssetIDNo,
		AssetIDNoCorection,
		ReverseGeoHouseNo,
		ReverseGeoStreet1,
		ReverseGeoCity,
		ReverseGeoState,
		ReverseGeoZip,
		ReverseGeoLat,
		ReverseGeoLong,
		ReverseGeoQuality,
		HouseNoNAFlag,
		HouseNo,
		Street1,
		Street2,
		AptSuite,
		AptDesc,
		Apt,
		City,
		State,
		ZIP,
		County,
		CountyCode,
		Photo1,
		Photo2,
		Photo3,
		ApprovedFlag,
		ApprovedByUserUID,
		ApprovedDTLT,
		SubmittedFlag,
		SubmittedStatusType,
		SubmittedUserUID,
		SubmittedDTLT,
		ResponseStatusType,
		Response,
		ResponceErrorDescription,
		ResponseDTLT,
		CompletedFlag,
		CompletedDTLT,
		GPSSource,
		GPSTime,
		FixQuality,
		NumberOfSatellites,
		HDOP,
		AltitudemetersAboveMeanSeaLevel,
		HeightofGeoid,
		TimeSecondsSinceLastDGPS,
		ChecksumData,
		Bearing,
		Speed,
		GPSStatus,
		NumberOfGPSAttempts,
		AOCFlag,
		CGIFlag,
		InspectionRequestUID,
		ActivityUID,
		SrcOpenDTLT,
		ReverseGeoLocationString,
		Grade1ReleaseReasonType,
		Grade1ReleaseDateTime
	)
	Select
		AssetAddressUID,
		AssetUID,
		AssetInspectionUID,
		MasterLeakLogUID,
		MapGridUID,
		ProjectID,
		SourceID,
		CreatedUserUID,
		@UserUID, --  ModifiedUserUID,
		@Datetime, --  SrcDTLT,
		--SrvDTLT,
		--SrvDTLTOffset,
		GPSType,
		GPSSentence,
		Latitude,
		Longitude,
		SHAPE,
		@Comments, --Comments,
		RevisionComments,
		@Revision,
		1, --ActiveFlag,
		StatusType,
		NewAssetFlag,
		NonAssetLocationFlag,
		AssetAddessCorrectionFlag,
		AssetIDNumberCorrectionFlag,
		AssetConfirmFlag,
		RouteNo,
		RouteSeq,
		SortOrder,
		AssetAccountNo,
		AssetAccountName,
		AssetName,
		AssetLocationID,
		AssetLocationCode,
		AssetIDStatus,
		AssetIDNo,
		AssetIDNoCorection,
		ReverseGeoHouseNo,
		ReverseGeoStreet1,
		ReverseGeoCity,
		ReverseGeoState,
		ReverseGeoZip,
		ReverseGeoLat,
		ReverseGeoLong,
		ReverseGeoQuality,
		HouseNoNAFlag,
		@HouseNo, --HouseNo,
		@Street, --Street1,
		Street2,
		@AptSuite, --AptSuite,
		AptDesc,
		Apt,
		@City, --City,
		State,
		ZIP,
		County,
		CountyCode,
		Photo1,
		Photo2,
		Photo3,
		ApprovedFlag,
		ApprovedByUserUID,
		ApprovedDTLT,
		SubmittedFlag,
		SubmittedStatusType,
		SubmittedUserUID,
		SubmittedDTLT,
		ResponseStatusType,
		Response,
		ResponceErrorDescription,
		ResponseDTLT,
		CompletedFlag,
		CompletedDTLT,
		GPSSource,
		GPSTime,
		FixQuality,
		NumberOfSatellites,
		HDOP,
		AltitudemetersAboveMeanSeaLevel,
		HeightofGeoid,
		TimeSecondsSinceLastDGPS,
		ChecksumData,
		Bearing,
		Speed,
		GPSStatus,
		NumberOfGPSAttempts,
		AOCFlag,
		CGIFlag,
		InspectionRequestUID,
		ActivityUID,
		SrcOpenDTLT,
		ReverseGeoLocationString,
		Grade1ReleaseReasonType,
		Grade1ReleaseDateTime
	from tgAssetAddress where AssetAddressUID = @AssetAddressUID and Revision = @Revision - 1

	
	Commit Transaction

	if(@ReturnVal = 1)
	BEGIN
		EXEC [spWebManagementLeakLogApproval] @AssetAddressIndicationUID, @UserUID
	END
End Try
Begin Catch
	Rollback Transaction
	Set @ReturnVal = 0
End Catch

/*******************************************************

   Last thing we do
	Drop the table created in this proceedure

******************************************************/

Drop Table #JSON_Parse

SET NOCOUNT OFF

Select @ReturnVal as Succeeded