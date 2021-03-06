﻿







CREATE Procedure [dbo].[spWebManagementMasterLeakLogSubmit]
(
@MasterLeakLogUID varchar(100)
,@SubmittedUID varchar(100)
--,@ReturnVal varchar(200) OUTPUT
)
AS

SET NOCOUNT ON

Declare @ReviewdStatusType varchar(200) = 'Reviewd'
	,@SubmitPending varchar(200) = 'Submit/Pending'
	,@ApprovedNotSubmitted varchar(200) = 'Approved/NotSubmitted'
	,@CompletedStatusType varchar(200) = 'Completed'
	,@TotalLeakCount int
	,@TotalGrade1Count int
	,@TotalNonGrade1Count int
	,@TotalNonGrade1CountNeedingSent int
	,@TotalLTNonGrade1Count int
	,@MasterLeakLogToSendCount int
	
	,@Revision int
	,@ReturnVal bit = 0
	,@AssetAddressIndicationUID varchar(200)
	,@City varchar(50)
	,@LeakNum varchar(20)
	,@BadCityName varchar(50)
	
	--Set @ReturnVal = 0


select
@TotalLeakCount = SUM(ISNULL(LeakCount, 0))
, @TotalGrade1Count = SUM(Grade1Count)
, @TotalNonGrade1Count = SUM(NonGrade1Count)
, @TotalNonGrade1CountNeedingSent = SUM(NonGrade1CountNeedingSent)
, @TotalLTNonGrade1Count = SUM(LTNonGrade1Count)
 from
(Select
Case WHEN aai.StatusType <> 'Completed' THEN 1 else 0 END [LeakCount],
CASE WHEN GradeType = '1' and aai.StatusType <> 'Completed' THEN 1 ELSE 0 END [Grade1Count],
CASE WHEN GradeType <> '1' and aai.StatusType <> 'Completed' THEN 1 ELSE 0 END [NonGrade1Count],
CASE WHEN GradeType <> '1' and aai.StatusType in ('Reviewed') THEN 1 ELSE 0 END [NonGrade1CountNeedingSent],
CASE WHEN mg.FLOC = 'GT.PHYS.TRNS.9999.0T99' and GradeType <> '1' THEN 1 else 0 END [LTNonGrade1Count]
from (Select * From tgAssetAddressIndication Where ActiveFlag = 1 and MasterLeakLogUID = @MasterLeakLogUID) aai
Left Join (select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on aai.MapGridUID = mg.MapGridUID) LeakCount

Select @MasterLeakLogToSendCount = Count(*)	from tMasterLeakLog	where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1 and StatusType in (@ApprovedNotSubmitted)
	
BEGIN TRY

	BEGIN TRANSACTION


--if there are no leaks mark the MasterLeakLog as completed	
	
	IF ISNULL(@TotalLeakCount, 0) = 0 --and @MasterLeakLogToSendCount = 1 
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
			SrcDTLT,
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
			'WEB', --SourceID,
			CreatedUserUID,
			@SubmittedUID,
			getdate(),
			Comments,
			RevisionComments,
			@Revision, -- Revision,
			1, --ActiveFlag,
			@CompletedStatusType, --StatusType,
			1, --ApprovedFlag,
			@SubmittedUID, --ApprovedByUserUID,
			getdate(), --ApprovedDTLT,
			0, --SubmittedFlag,
			'', --SubmittedStatusType,
			'', --SubmittedUserUID,
			NULL, --SubmittedDTLT,
			ResponseStatusType,
			Response,
			ResponceErrorDescription,
			ResponseDTLT,
			1, --CompletedFlag,
			getdate() --CompletedDTLT
		From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and revision = @Revision - 1

		Set @ReturnVal = 1
	
	END
	ELSE IF @MasterLeakLogToSendCount = 1  

--There are leaks and the Master Leak Log is to send	

		IF @TotalLeakCount = @TotalGrade1Count + @TotalLTNonGrade1Count --All leaks are Grade 1 or LT. Mark Grade 1 and LT Leaks and Master Leak Log as Completed
		BEGIN

--There are only grade one leaks.  Mark Master Leak Log and all Grade 1 Leaks as completed.
		
			Update tgAssetAddressIndication set ActiveFlag = 0 where MasterLeakLogUID = @MasterLeakLogUID and GradeType = '1'

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
				AAI.AssetAddressIndicationUID,
				AssetAddressUID,
				InspectionRequestUID,
				MapGridUID,
				MasterLeakLogUID,
				ProjectID,
				'WEB', --SourceID,
				CreatedUserUID,
				@SubmittedUID, --ModifiedUserUID,
				getdate(), --SrcDTLT,
				NULL, --SrcOpenDTLT,
				NULL, --SrcClosedDTLT,
				GPSType,
				GPSSentence,
				Latitude,
				Longitude,
				SHAPE,
				Comments,
				RevisionComments,
				NextRev.NextRevision, -- Revision,
				1, --ActiveFlag,
				@CompletedStatusType, -- StatusType,
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
				'', --SubmittedStatusType,
				@SubmittedUID, --SubmittedUserUID,
				getdate(), --SubmittedDTLT,
				ResponseStatusType,
				ResponseComments,
				ResponceErrorComments,
				ResponseDTLT,
				1, --CompletedFlag,
				Getdate(), --CompletedDTLT,
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
			From tgAssetAddressIndication aai
			Join (select AssetAddressIndicationUID, Count(*) NextRevision 
					from tgAssetAddressIndication aai 
					Left Join (select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on aai.MapGridUID = mg.MapGridUID
					Where MasterLeakLogUID = @MasterLeakLogUID and (GradeType = '1' or mg.FLOC = 'GT.PHYS.TRNS.9999.0T99')
					group by AssetAddressIndicationUID) NextRev on NextRev.AssetAddressIndicationUID = aai.AssetAddressIndicationUID and aai.Revision = NextRev.NextRevision - 1


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
				SrcDTLT,
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
				'WEB', --SourceID,
				CreatedUserUID,
				@SubmittedUID,
				getdate(),
				Comments,
				RevisionComments,
				@Revision, -- Revision,
				1, --ActiveFlag,
				@CompletedStatusType, --StatusType,
				ApprovedFlag,
				ApprovedByUserUID,
				ApprovedDTLT,
				SubmittedFlag,
				SubmittedStatusType,
				'', --SubmittedUserUID,
				NULL, --SubmittedDTLT,
				ResponseStatusType,
				Response,
				ResponceErrorDescription,
				ResponseDTLT,
				1, --CompletedFlag,
				getdate() --CompletedDTLT
			From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and revision = @Revision - 1

			Set @ReturnVal = 1

		END
		ELSE IF @TotalLeakCount = (@TotalGrade1Count + @TotalNonGrade1CountNeedingSent) 
		BEGIN

-- There appears to be a mix of leak Grades

-- Check to see if city is correct on non grade 1 leaks

			IF (Select Count(*)
				From 
				(Select * from [dbo].[tgAssetAddressIndication] aai
				 where aai.ActiveFlag = 1 and aai.MasterLeakLogUID = @MasterLeakLogUID and aai.GradeType <> '1') aai
				Join (select AssetAddressUID, City from tgAssetAddress where ActiveFlag = 1) aa on aai.AssetAddressUID = aa.AssetAddressUID
				Left Join [dbo].[rCityCounty] cc on aa.City = cc.city
				Left Join (select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on aai.MapGridUID = mg.MapGridUID
				Where cc.City is null and mg.FLOC <> 'GT.PHYS.TRNS.9999.0T99') > 0
			BEGIN 
--City isn't correct on some of the leaks.  Mark Leaks with bad city and exit proc

	--Have to create cursor here

					Declare curBadCity Cursor Static
					For
					Select AssetAddressIndicationUID, aa.City
					From
					(Select * from [dbo].[tgAssetAddressIndication] aai
						where aai.ActiveFlag = 1 and aai.MasterLeakLogUID = @MasterLeakLogUID and aai.GradeType <> '1') aai
					Join (select AssetAddressUID, City from tgAssetAddress where ActiveFlag = 1) aa on aai.AssetAddressUID = aa.AssetAddressUID
					Left Join [dbo].[rCityCounty] cc on aa.City = cc.city
					Left Join (select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on aai.MapGridUID = mg.MapGridUID
					Where cc.City is null and mg.FLOC <> 'GT.PHYS.TRNS.9999.0T99'


					/*
					Update aai set ActiveFlag = 0
					From [dbo].[tgAssetAddressIndication] aai
					Join (Select aai.AssetAddressIndicationUID
							From 
							(Select * from [dbo].[tgAssetAddressIndication] aai
							 where aai.ActiveFlag = 1 and aai.MasterLeakLogUID = @MasterLeakLogUID and aai.GradeType <> '1') aai
							Join (select AssetAddressUID, City from tgAssetAddress where ActiveFlag = 1) aa on aai.AssetAddressUID = aa.AssetAddressUID
							Left Join [dbo].[rCityCounty] cc on aa.City = cc.city
							Left Join (select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on aai.MapGridUID = mg.MapGridUID
							Where cc.City is null and mg.FLOC <> 'GT.PHYS.TRNS.9999.0T99') BadCity on badcity.AssetAddressIndicationUID = aai.AssetAddressIndicationUID

					*/

					Open curBadCity

					Fetch Next from curBadCity Into @AssetAddressIndicationUID, @BadCityName

					WHILE @@FETCH_STATUS = 0
					BEGIN

						Update tgAssetAddressIndication Set ActiveFlag = 0 Where AssetAddressIndicationUID = @AssetAddressIndicationUID

						select @Revision = count(*) From tgAssetAddressIndication Where AssetAddressIndicationUID = @AssetAddressIndicationUID

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
							aai.AssetAddressIndicationUID,
							aai.AssetAddressUID,
							aai.InspectionRequestUID,
							aai.MapGridUID,
							aai.MasterLeakLogUID,
							aai.ProjectID,
							'WEB', --aai.SourceID,
							aai.CreatedUserUID,
							@SubmittedUID,
							getdate(), --aai.SrcDTLT,
							aai.SrcOpenDTLT,
							aai.SrcClosedDTLT,
							aai.GPSType,
							aai.GPSSentence,
							aai.Latitude,
							aai.Longitude,
							aai.SHAPE,
							aai.Comments,
							'Rejected - Leak Number ' + ISNULL(aai.LeakNo, '') + ' - Unknown City ' + @BadCityName, -- RevisionComments,
							@Revision, -- NextRev.NextRevision,
							1, --ActiveFlag,
							'Pending', --StatusType,
							aai.ManualMapPlat,
							aai.PipelineType,
							aai.SurveyType,
							aai.Map,
							aai.Plat,
							aai.RecordedMap,
							aai.RecordedPlat,
							aai.RecordedBlock,
							aai.LandmarkType,
							aai.Route,
							aai.Line,
							aai.HouseNoNAFlag,
							aai.HouseNo,
							aai.Street1,
							aai.City,
							aai.DescriptionReadingLocation,
							aai.County,
							aai.CountyCode,
							aai.FacilityType,
							aai.LocationType,
							aai.InitialLeakSourceType,
							aai.ReportedByType,
							aai.LeakNo,
							aai.SAPNo,
							aai.PavedType,
							aai.SORLType,
							aai.SORLOther,
							aai.Within5FeetOfBuildingType,
							aai.SuspectedCopperType,
							aai.EquipmentFoundByUID,
							aai.FoundBy,
							aai.FoundBySerialNumber,
							aai.InstrumentTypeGradeByType,
							aai.EquipmentGradeByUID,
							aai.GradeBy,
							aai.GradeBySerialNumber,
							aai.ReadingGrade,
							aai.GradeType,
							aai.InfoCodesType,
							aai.PotentialHCAType,
							aai.Grade2PlusRequested,
							aai.TwoPercentOrLessSuspectCopperFlag,
							aai.LeakDownGradedFlag,
							aai.HCAConstructionSupervisorUserUID,
							aai.HCADistributionPlanningEngineerUserUID,
							aai.HCAPipelineEngineerUserUID,
							aai.Photo1,
							aai.Photo2,
							aai.Photo3,
							0, --ApprovedFlag,
							'', --ApprovedByUserUID,
							NULL, --ApprovedDTLT,
							aai.SubmittedFlag,
							aai.SubmittedStatusType,
							aai.SubmittedUserUID,
							aai.SubmittedDTLT,
							aai.ResponseStatusType,
							aai.ResponseComments,
							aai.ResponceErrorComments,
							aai.ResponseDTLT,
							aai.CompletedFlag,
							aai.CompletedDTLT,
							aai.AboveBelowGroundType,
							aai.FoundDateTime,
							aai.GPSSource,
							aai.GPSTime,
							aai.FixQuality,
							aai.NumberOfSatellites,
							aai.HDOP,
							aai.AltitudemetersAboveMeanSeaLevel,
							aai.HeightOfGeoid,
							aai.TimeSecondsSinceLastDGPS,
							aai.ChecksumData,
							aai.Bearing,
							aai.Speed,
							aai.GPSStatus,
							aai.NumberOfGPSAttempts,
							aai.ActivityUID,
							aai.AssetInspectionUID,
							aai.MapPlatLeakNumber,
							0, --LockedFlag,
							aai.SAPComments,
							aai.StationBegin,
							aai.StationEnd
						From tgAssetAddressIndication aai Where aai.AssetAddressIndicationUID = @AssetAddressIndicationUID and aai.Revision = @Revision - 1

						Fetch Next from curBadCity Into @AssetAddressIndicationUID, @BadCityName

					END
					
					Close curBadCity
					Deallocate curBadCity

				END
				ELSE
				BEGIN --All Cities are correct.  Move this Master Leak Log and all Leaks to Submitted/Pending

					Update tgAssetAddressIndication set ActiveFlag = 0 where MasterLeakLogUID = @MasterLeakLogUID 

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
						AAI.AssetAddressIndicationUID,
						AssetAddressUID,
						InspectionRequestUID,
						AAI.MapGridUID,
						MasterLeakLogUID,
						AAI.ProjectID,
						'WEB', --SourceID,
						AAI.CreatedUserUID,
						@SubmittedUID, --ModifiedUserUID,
						getdate(), --SrcDTLT,
						NULL, --SrcOpenDTLT,
						NULL, --SrcClosedDTLT,
						GPSType,
						GPSSentence,
						Latitude,
						Longitude,
						AAI.SHAPE,
						AAI.Comments,
						AAI.RevisionComments,
						NextRev.NextRevision, -- Revision,
						1, --ActiveFlag,
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN  @CompletedStatusType ELSE @SubmitPending END   , -- StatusType,
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
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN 0 ELSE 1 END SubmittedFlag,
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN @SubmitPending ELSE '' END, --  SubmittedStatusType,
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN '' ELSE @SubmittedUID END, -- SubmittedUserUID,
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN NULL ELSE getdate() END, --SubmittedDTLT,
						ResponseStatusType,
						ResponseComments,
						ResponceErrorComments,
						ResponseDTLT,
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN 1 ELSE 0 END, --CompletedFlag,
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN Getdate() ELSE NULL END, --CompletedDTLT,
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
						CASE WHEN aai.GradeType = '1' OR mg.FLOC = 'GT.PHYS.TRNS.9999.0T99'  THEN  1 ELSE 0 END, --LockedFlag,
						SAPComments,
						StationBegin,
						StationEnd
					From tgAssetAddressIndication aai
					Join (select AssetAddressIndicationUID, Count(*) NextRevision 
							from tgAssetAddressIndication aai 
							Where MasterLeakLogUID = @MasterLeakLogUID 
							group by AssetAddressIndicationUID) NextRev on NextRev.AssetAddressIndicationUID = aai.AssetAddressIndicationUID and aai.Revision = NextRev.NextRevision - 1
					Left Join (select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on aai.MapGridUID = mg.MapGridUID

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
						SrcDTLT,
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
						'WEB', --SourceID,
						CreatedUserUID,
						@SubmittedUID,
						getdate(),
						Comments,
						RevisionComments,
						@Revision, -- Revision,
						1, --ActiveFlag,
						@SubmitPending, --StatusType,
						1, --ApprovedFlag,
						ApprovedByUserUID,
						ApprovedDTLT,
						1, --SubmittedFlag,
						@SubmitPending, -- SubmittedStatusType,
						@SubmittedUID, --SubmittedUserUID,
						getdate(), --SubmittedDTLT,
						ResponseStatusType,
						Response,
						ResponceErrorDescription,
						ResponseDTLT,
						0, --CompletedFlag,
						NULL --CompletedDTLT
					From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and revision = @Revision - 1

					Set @ReturnVal = 1

				END

			END

		COMMIT TRANSACTION
END TRY
BEGIN CATCH

	ROLLBACK TRANSACTION

	SET @ReturnVal = 0
	
END CATCH	

SET NOCOUNT OFF

--Select @ReturnVal as Succeeded

Select @ReturnVal As Succeeded, StatusType From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1