



CREATE Procedure [dbo].[spWebManagementLeakLogApproval]
(
@AddressIndicationUID varchar(100)
,@ApproverUID varchar(100)
--,@ReturnVal varchar(200) OUTPUT
)
AS

SET NOCOUNT ON

Declare @ReviewdStatusType varchar(200) = 'Reviewed'
	,@ApprovedNotSubmitted varchar(200) = 'ApprovedNotSubmitted'
	,@Revision int
	,@ReturnVal bit = 1
	,@MasterLeakLogUID varchar(100)
	

	--Set @ReturnVal = 1

	Select @Revision = Count(*) From [dbo].[tgAssetAddressIndication] where AssetAddressIndicationUID =  @AddressIndicationUID

	Select @MasterLeakLogUID = MasterLeakLogUID From [dbo].[tgAssetAddressIndication] where AssetAddressIndicationUID =  @AddressIndicationUID and ActiveFlag = 1


	BEGIN TRY

		BEGIN TRANSACTION

			Update [dbo].[tgAssetAddressIndication] set ActiveFlag = 0 where AssetAddressIndicationUID =  @AddressIndicationUID

			Insert Into [dbo].[tgAssetAddressIndication]
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
				SAPComments	
				
				
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
				@ApproverUID,
				
				GPSType,
				GPSSentence,
				Latitude,
				Longitude,
				SHAPE,
				Comments,
				RevisionComments,
				@Revision, --Revision,
				1, --ActiveFlag,
				@ReviewdStatusType, -- StatusType,
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
				1 , --ApprovedFlag,
				@ApproverUID, --ApprovedByUserUID,
				Getdate(), --ApprovedDTLT,
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
				SAPComments	
			From [dbo].[tgAssetAddressIndication] where AssetAddressIndicationUID =  @AddressIndicationUID and Revision = @Revision - 1

			IF (Select Count(*) 
				from [dbo].[tgAssetAddressIndication] 
				where MasterLeakLogUID =  @MasterLeakLogUID
					and ActiveFlag = 1 
					and StatusType in ('In Progress','Pending') ) = 0

			BEGIN

				Select @Revision = Count(*) From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID

				Update tMasterLeakLog set ActiveFlag = 0 where MasterLeakLogUID = @MasterLeakLogUID

				Insert Into tMasterLeakLog
				(
					MasterLeakLogUID,
					InspectionRequestLogUID,
					MapGridUID,
					ServiceDate,
					ProjectID,
					SourceID,
					CreatedUserUID,
					ModifiedUserUID,
					Comments,
					RevisionComments,
					Revision,
					ActiveFlag,
					StatusType,
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
					CompletedDTLT
				)
				Select 
					MasterLeakLogUID,
					InspectionRequestLogUID,
					MapGridUID,
					ServiceDate,
					ProjectID,
					SourceID,
					CreatedUserUID,
					@ApproverUID,
					Comments,
					RevisionComments,
					@Revision, -- Revision,
					1, --ActiveFlag,
					@ApprovedNotSubmitted, --StatusType,
					1, --ApprovedFlag,
					@ApproverUID, --ApprovedByUserUID,
					getdate(), --ApprovedDTLT,
					SubmittedFlag,
					SubmittedStatusType,
					SubmittedUserUID,
					SubmittedDTLT,
					ResponseStatusType,
					Response,
					ResponceErrorDescription,
					ResponseDTLT,
					CompletedFlag,
					CompletedDTLT
				From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and revision = @Revision - 1

			END

		COMMIT TRANSACTION

	END TRY
	BEGIN CATCH

		ROLLBACK TRANSACTION
		Set @ReturnVal = 0

	END CATCH

SET NOCOUNT OFF

Select @ReturnVal As Succeeded, StatusType From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1