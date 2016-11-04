



CREATE Procedure [dbo].[spWebManagementMasterLeakLogSubmit]
(
@MasterLeakLogUID varchar(100)
,@SubmittedUID varchar(100)
--,@ReturnVal varchar(200) OUTPUT
)
AS

SET NOCOUNT ON

Declare @ReviewdStatusType varchar(200) = 'Reviewd'
	,@SubmitPending varchar(200) = 'SubmitPending'
	,@ApprovedNotSubmitted varchar(200) = 'ApprovedNotSubmitted'
	,@Revision int
	,@ReturnVal bit = 0
	,@AssetAddressIndicationUID varchar(100)
	,@City varchar(50)
	,@LeakNum varchar(20)
	
	--Set @ReturnVal = 0
	
	
	IF (Select Count(*)
				from [dbo].[tgAssetAddressIndication] 
				where MasterLeakLogUID = @MasterLeakLogUID 
					and ActiveFlag = 1 
					and StatusType in ('In Progress','Pending')) = 0
		AND (Select Count(*)
				from tMasterLeakLog
				where MasterLeakLogUID = @MasterLeakLogUID 
					and ActiveFlag = 1 
					and StatusType in (@ApprovedNotSubmitted)) = 1
	
		BEGIN
	
		
			IF (Select Count(*)
				From 
				(Select City from [dbo].[tgAssetAddressIndication] where ActiveFlag = 1 and MasterLeakLogUID = @MasterLeakLogUID) aai
				Left Join [dbo].[rCityCounty] cc on aai.City = cc.city
				Where cc.City is null) > 0
		
			BEGIN

				Declare curBadCity Cursor
				For
				Select aai.AssetAddressIndicationUID, aai.City, aai.LeakNo
				From 
				(Select * from [dbo].[tgAssetAddressIndication] where ActiveFlag = 1 and MasterLeakLogUID = @MasterLeakLogUID) aai
				Left Join [dbo].[rCityCounty] cc on aai.City = cc.city
				Where cc.City is null
		
				Open curBadCity

				Fetch Next From curBadCity into @AssetAddressIndicationUID, @City, @LeakNum

				While @@FETCH_STATUS = 0
				BEGIN

					Update tgAssetAddressIndication set ActiveFlag = 0 where AssetAddressIndicationUID = @AssetAddressIndicationUID

					Select @Revision = Count(*) From tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID

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
						SrcOpenDTLT,
						SrcClosedDTLT,
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
						@SubmittedUID,
						SrcDTLT,
						SrcOpenDTLT,
						SrcClosedDTLT,
						GPSType,
						GPSSentence,
						Latitude,
						Longitude,
						SHAPE,
						Comments,
						'Rejected - Leak Number ' + @LeakNum + ' - Unknown City (' + @City + ')', -- RevisionComments,
						@Revision,
						1, --ActiveFlag,
						'Pending', --StatusType,
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
						0, --ApprovedFlag,
						'', --ApprovedByUserUID,
						NULL, --ApprovedDTLT,
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
						0, --LockedFlag,
						SAPComments,
						StationBegin,
						StationEnd
					From tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID and Revision = @Revision - 1


					Fetch Next From curBadCity into @AssetAddressIndicationUID, @City, @LeakNum


				END

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
					@SubmittedUID,
					Comments,
					RevisionComments,
					@Revision, -- Revision,
					1, --ActiveFlag,
					'NotApproved', --StatusType,
					0, --ApprovedFlag,
					'', --ApprovedByUserUID,
					NULL, --ApprovedDTLT,
					0, --SubmittedFlag,
					SubmittedStatusType,
					'', --SubmittedUserUID,
					NULL, --SubmittedDTLT,
					ResponseStatusType,
					Response,
					ResponceErrorDescription,
					ResponseDTLT,
					0, --CompletedFlag,
					NULL --CompletedDTLT
				From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and revision = @Revision - 1
				
		
			END
			ELSE
			BEGIN
		
				Declare curLeakLogs Cursor
				For
				Select AssetAddressIndicationUID from [dbo].[tgAssetAddressIndication] where ActiveFlag = 1 and MasterLeakLogUID = @MasterLeakLogUID
		
				Open curLeakLogs

				Fetch Next From curLeakLogs into @AssetAddressIndicationUID

				While @@FETCH_STATUS = 0
				BEGIN

					Update tgAssetAddressIndication set ActiveFlag = 0 where AssetAddressIndicationUID = @AssetAddressIndicationUID

					Select @Revision = Count(*) From tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID

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
						SrcOpenDTLT,
						SrcClosedDTLT,
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
						@SubmittedUID,
						SrcDTLT,
						SrcOpenDTLT,
						SrcClosedDTLT,
						GPSType,
						GPSSentence,
						Latitude,
						Longitude,
						SHAPE,
						Comments,
						'', -- RevisionComments,
						@Revision,
						1, --ActiveFlag,
						'Submitted', --StatusType,
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
						ApprovedFlag,
						ApprovedByUserUID,
						ApprovedDTLT,
						1, --SubmittedFlag,
						SubmittedStatusType,
						@SubmittedUID, --SubmittedUserUID,
						getdate(), --SubmittedDTLT,
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
						1, --LockedFlag,
						SAPComments,
						StationBegin,
						StationEnd
					From tgAssetAddressIndication where AssetAddressIndicationUID = @AssetAddressIndicationUID and Revision = @Revision - 1


					Fetch Next From curLeakLogs into @AssetAddressIndicationUID


				END

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
					@SubmittedUID,
					Comments,
					RevisionComments,
					@Revision, -- Revision,
					1, --ActiveFlag,
					@SubmitPending, --StatusType,
					ApprovedFlag,
					ApprovedByUserUID,
					ApprovedDTLT,
					1, --SubmittedFlag,
					SubmittedStatusType,
					@SubmittedUID, --SubmittedUserUID,
					getdate(), --SubmittedDTLT,
					ResponseStatusType,
					Response,
					ResponceErrorDescription,
					ResponseDTLT,
					CompletedFlag,
					CompletedDTLT
				From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and revision = @Revision - 1

				Set @ReturnVal = 1

			END

		END

SET NOCOUNT OFF

--Select @ReturnVal as Succeeded

Select @ReturnVal As Succeeded, StatusType From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1