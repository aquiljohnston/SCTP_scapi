Create Procedure spWebManagementMapStampSubmit
(
@InspectionRequestUID varchar(100)
,@SubmittedUID varchar(100)
)
AS
Declare @InProgressStatusType varchar(200) = 'In Progress'
	,@SubmitPendingStatusType varchar(200) = 'Submit/Pending'
	--,@ApprovedNotSubmitted varchar(200) = 'ApprovedNotSubmitted'
	,@Revision int
	,@ReturnVal bit = 0
	
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
					ModifiedUserUID,
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

				COMMIT TRANSACTION

				Set @ReturnVal = 1

			END TRY
			BEGIN CATCH

			ROLLBACK TRANSACTION
			Set @ReturnVal = 0

			END CATCH

		END

Return @ReturnVal