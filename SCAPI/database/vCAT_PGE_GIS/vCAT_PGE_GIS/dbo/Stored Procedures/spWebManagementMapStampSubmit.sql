


CREATE Procedure [dbo].[spWebManagementMapStampSubmit]
(
@InspectionRequestUID varchar(100)
,@SubmittedUID varchar(100)
--,@ReturnVal varchar(200) OUTPUT
)
AS

SET NOCOUNT ON

Declare @InProgressStatusType varchar(200) = 'In Progress'
	,@SubmitPendingStatusType varchar(200) = 'Submit/Pending'
	--,@ApprovedNotSubmitted varchar(200) = 'ApprovedNotSubmitted'
	,@Revision int
	,@ReturnVal bit = 0

	--Set @ReturnVal = 0
	
	IF (Select Count(*)
				from [dbo].[tInspectionService]
				where InspectionRequestUID = @InspectionRequestUID 
					and ActiveFlag = 1 
					and StatusType in (@InProgressStatusType)) = 0
		
	
	BEGIN
	
		Select @Revision = Count(*) From tInspectionRequest where InspectionRequestUID = @InspectionRequestUID

		BEGIN TRY

			BEGIN TRANSACTION

				Update tInspectionRequest set ActiveFlag = 0 where InspectionRequestUID = @InspectionRequestUID

				Insert Into tInspectionRequest
				(
					InspectionRequestUID,
					MapGridUID,
					ProjectID,
					SourceID,
					CreatedUserUID,
					ModifiedUserUID,
					CreateDTLT,
					ModifiedDTLT,
					Comments,
					RevisionComments,
					Revision,
					ActiveFlag,
					StatusType,
					PipelineType,
					SurveyType,
					LsNtfNo,
					OrderNo,
					MapID,
					Wall,
					Plat,
					MWC,
					FLOC,
					InspectionFrequencyType,
					ComplianceDueDate,
					ScheduledStartDate,
					ScheduledCompleteDate,
					ReleaseDate,
					PrevServ,
					PrevFtOfMain,
					ReturnFlag,
					ReturnComments,
					FileCount,
					ApprovedFlag,
					ApprovedByUserUID,
					ApprovedDTLT,
					SubmittedFlag,
					SubmittedStatusType,
					SubmittedUserUID,
					SubmittedDTLT,
					ReturnedFlag,
					ReturnedFromPGEStatusType,
					RetrunedFromPGEDTLT,
					CompletedFlag,
					CompletedDTLT,
					InspectionType,
					ActualStartDate
				)
				Select
					InspectionRequestUID,
					MapGridUID,
					ProjectID,
					SourceID,
					CreatedUserUID,
					@SubmittedUID, --ModifiedUserUID,
					CreateDTLT,
					ModifiedDTLT,
					Comments,
					RevisionComments,
					@Revision, -- Revision,
					1, --ActiveFlag,
					@SubmitPendingStatusType, --StatusType,
					PipelineType,
					SurveyType,
					LsNtfNo,
					OrderNo,
					MapID,
					Wall,
					Plat,
					MWC,
					FLOC,
					InspectionFrequencyType,
					ComplianceDueDate,
					ScheduledStartDate,
					ScheduledCompleteDate,
					ReleaseDate,
					PrevServ,
					PrevFtOfMain,
					ReturnFlag,
					ReturnComments,
					FileCount,
					ApprovedFlag,
					ApprovedByUserUID,
					ApprovedDTLT,
					1, --SubmittedFlag,
					SubmittedStatusType,
					@SubmittedUID, --SubmittedUserUID,
					getdate(), --SubmittedDTLT,
					ReturnedFlag,
					ReturnedFromPGEStatusType,
					RetrunedFromPGEDTLT,
					CompletedFlag,
					CompletedDTLT,
					InspectionType,
					ActualStartDate
				From tInspectionRequest where InspectionRequestUID = @InspectionRequestUID and Revision = @Revision - 1

-- Now we lock all of the MapStamp Records

				Update [dbo].[tInspectionService] Set ActiveFlag = 0 Where InspectionRequestUID = @InspectionRequestUID

				Update [dbo].[tMapStampPicaro] Set ActiveFlag = 0 Where InspectionRequestUID = @InspectionRequestUID
				
				Insert Into [dbo].[tInspectionService]
				(
					InspectionServicesUID,
					MasterLeakLogUID,
					MapGridUID,
					InspectionRequestUID,
					InspectionEquipmentUID,
					ProjectID,
					SourceID,
					CreatedUserUID,
					ModifiedUserUID,
					Comments,
					RevisionComments,
					Revision,
					ActiveFlag,
					StatusType,
					EquipmentType,
					InstrumentType,
					SerialNumber,
					CalibrationLevel,
					CalibrationVerificationFlag,
					WindSpeedStart,
					WindSpeedEnd,
					EquipmentModeType,
					EstimatedFeet,
					EstimatedServices,
					EstimatedHours,
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
					SurveyMode,
					PlaceHolderFlag,
					WindSpeedStartUID,
					WindSpeedMidUID,
					MapAreaNumber,
					LockedFlag,
					TaskOutUID,
					CreateDateTime
				)
				Select
					[is].InspectionServicesUID,
					MasterLeakLogUID,
					MapGridUID,
					InspectionRequestUID,
					InspectionEquipmentUID,
					ProjectID,
					SourceID,
					CreatedUserUID,
					@SubmittedUID, --ModifiedUserUID,
					Comments,
					RevisionComments,
					NextRev.NextRevision, -- Revision,
					1, --ActiveFlag,
					@SubmitPendingStatusType, --StatusType,
					EquipmentType,
					InstrumentType,
					SerialNumber,
					CalibrationLevel,
					CalibrationVerificationFlag,
					WindSpeedStart,
					WindSpeedEnd,
					EquipmentModeType,
					EstimatedFeet,
					EstimatedServices,
					EstimatedHours,
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
					SurveyMode,
					PlaceHolderFlag,
					WindSpeedStartUID,
					WindSpeedMidUID,
					MapAreaNumber,
					1, --LockedFlag,
					TaskOutUID,
					CreateDateTime
				From [dbo].[tInspectionService] [is]
				Join (Select InspectionServicesUID, count(*) NextRevision from [dbo].[tInspectionService] 
					Where InspectionRequestUID = @InspectionRequestUID
					Group By InspectionServicesUID) NextRev on [is].InspectionServicesUID = NextRev.InspectionServicesUID and [is].Revision = NextRev.NextRevision - 1
				
				
				Insert Into [dbo].[tMapStampPicaro]
				(
					MapStampPicaroUID,
					InspectionRequestUID,
					MapStampUID,
					ProjectID,
					CreatedByUserUID,
					ModifiedByUserUID,
					CreatedDateTime,
					ModifiedDateTime,
					Revision,
					ActiveFlag,
					PicaroEquipmentID,
					FeetOfMain,
					Services,
					WindSpeedStart,
					WindSpeedMid,
					StatusType,
					SurveyorUID,
					SurveyDate,
					Seq,
					LockedFlag
				)
				Select 
					msp.MapStampPicaroUID,
					InspectionRequestUID,
					MapStampUID,
					ProjectID,
					CreatedByUserUID,
					@SubmittedUID, --ModifiedByUserUID,
					CreatedDateTime,
					getdate(), --ModifiedDateTime,
					NextRev.NextRevision, -- Revision,
					1, --ActiveFlag,
					PicaroEquipmentID,
					FeetOfMain,
					Services,
					WindSpeedStart,
					WindSpeedMid,
					@SubmitPendingStatusType,
					SurveyorUID,
					SurveyDate,
					Seq,
					1 --LockedFlag
				From [dbo].[tMapStampPicaro] msp
				Join (Select MapStampPicaroUID, count(*) NextRevision from  [dbo].[tMapStampPicaro] 
					Where InspectionRequestUID = @InspectionRequestUID
					Group By MapStampPicaroUID) NextRev on msp.MapStampPicaroUID = NextRev.MapStampPicaroUID and msp.Revision = NextRev.NextRevision - 1	
				
				
				
				
				COMMIT TRANSACTION

				Set @ReturnVal = 1

			END TRY
			BEGIN CATCH

			ROLLBACK TRANSACTION
			Set @ReturnVal = 0

			END CATCH

		END

SET NOCOUNT OFF

Select @ReturnVal as Succeeded, StatusType From tInspectionRequest where InspectionRequestUID = @InspectionRequestUID and ActiveFlag = 1