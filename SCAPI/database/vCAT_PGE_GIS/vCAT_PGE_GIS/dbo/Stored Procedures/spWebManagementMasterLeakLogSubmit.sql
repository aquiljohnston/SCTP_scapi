
CREATE Procedure [dbo].[spWebManagementMasterLeakLogSubmit]
(
@MasterLeakLogUID varchar(100)
,@SubmittedUID varchar(100)
,@ReturnVal varchar(200) OUTPUT
)
AS
Declare @ReviewdStatusType varchar(200) = 'Reviewd'
	,@SubmitPending varchar(200) = 'SubmitPending'
	,@ApprovedNotSubmitted varchar(200) = 'ApprovedNotSubmitted'
	,@Revision int
	--,@ReturnVal bit = 0
	
	Set @ReturnVal = 0
	
	
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
	
		Select @Revision = Count(*) From tMasterLeakLog where MasterLeakLogUID = @MasterLeakLogUID

		BEGIN TRY

			BEGIN TRANSACTION

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

				COMMIT TRANSACTION

				Set @ReturnVal = 1

			END TRY
			BEGIN CATCH

			ROLLBACK TRANSACTION
			Set @ReturnVal = 0

			END CATCH

		END

--Return @ReturnVal