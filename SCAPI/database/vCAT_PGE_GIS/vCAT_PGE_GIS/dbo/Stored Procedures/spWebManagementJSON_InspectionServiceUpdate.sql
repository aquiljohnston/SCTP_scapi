





CREATE PROCEDURE [dbo].[spWebManagementJSON_InspectionServiceUpdate]
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

	
		--Select @ClientID = 1
		--Select @ClientID = StringValue From #JSON_Parse Where Name = 'ClientID'
		--Select Top 1 @UserUID = StringValue From #JSON_Parse Where Name = 'ActivityCreatedUserUID'


		Insert Into [dbo].[tWEBDataInsertArchive] (
			CreatedUserUID
			,TransactionType
			,InsertedData
			)
		Values (
			0
			,'InspectionServicesUpdate'
			,@JSON_Str
			)



/***************************************************************************

--  Create the unique function needed for the record type below this line

***************************************************************************/

		Declare 
			@InspectionServiceUID varchar(100) 
			,@SurveyMode varchar(25)
			,@WindSpeedStart varchar(10)
			,@WindSpeedMid varchar(10)
			,@FeetOfMain varchar(25)
			,@NumberOfServices varchar(10)
			,@Hours varchar(25)
			,@Date varchar(25)
			,@SurveyorLANID varchar(25)
			,@WindSpeedStartUID varchar(100)
			,@WindSpeedMidUID varchar(100)
			,@UserUID varchar(100)
			,@NewWindSpeedStartUID varchar(100)
			,@NewWindSpeedMidUID varchar(100)
			,@NextID int
			,@SupervisorUID varchar(100)
			,@InspectionRequestUID varchar(100)
			,@MapGridUID varchar(100)
			,@Revision int
			,@CurrentWindSpeedStart float
			,@CurrentWindSpeedMid float
			,@ReturnVal bit = 1
			,@MapAreaNumber varchar(10)
			,@SurveyType varchar(25)
			,@ApproverUID varchar(100)
			,@ApprovedDatetime varchar(25)
			,@PicaroEquipmentID varchar(100)
			--,@SurveyorLANID varchar(20)
			,@SurveyorUID varchar(200)
			,@DateSurveyed date



		Select @InspectionServiceUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'InspectionServicesUID'), '')
		Select @SurveyMode = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SurveyMode'), '')
		Select @WindSpeedStart = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WindSpeedStart'), '')
		Select @WindSpeedMid = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WindSpeedMid'), '')
		Select @FeetOfMain = ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMain'), '')
		Select @NumberOfServices = ISNULL((Select StringValue From #JSON_Parse Where Name = 'NumberOfServices'), '')
		Select @Hours = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Hours'), '')
		Select @Date = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Date'), '')
		Select @SurveyorLANID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SurveyorLANID'), '')
		Select @DateSurveyed = ISNULL((Select StringValue From #JSON_Parse Where Name = 'DateSurveyed'), '')
		Select @MapAreaNumber = ISNULL((Select StringValue From #JSON_Parse Where Name = 'MapAreaNumber'), '')
		Select @SurveyType = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SurveyType'), '')
		Select @ApprovedDatetime = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Date'), '')
		Select @ApproverUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'UserUID'), '')
		Select @PicaroEquipmentID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'PicaroEquipmentID'), '')

		If EXISTS(select * from UserTb where UserLANID = @SurveyorLANID)
		BEGIN
			select @SurveyorUID = UserUID from UserTb where UserLANID = @SurveyorLANID
		END

Begin Try

	Begin Transaction

--Get the current wind speed UIDs

		Select @InspectionRequestUID = InspectionRequestUID, @MapGridUID = MapGridUID, @WindSpeedStartUID = WindSpeedStartUID, @WindSpeedMidUID = WindSpeedMidUID from [dbo].[tInspectionService] where InspectionEquipmentUID = @InspectionServiceUID
		/*
		If ISNULL(@WindSpeedStartUID, '') <> ''
		BEGIN
			Select @CurrentWindSpeedStart WindSpeed from tgWindSpeed where WindSpeedUID = @WindSpeedStartUID and ActiveFlag = 1
		END

		If ISNULL(@WindSpeedMidUID, '') <> ''
		BEGIN
			Select @CurrentWindSpeedStart WindSpeed from tgWindSpeed where WindSpeedUID = @WindSpeedMidUID and ActiveFlag = 1
		END
		*/


		Select @NewWindSpeedStartUID = @WindSpeedStartUID, @NewWindSpeedMidUID = @WindSpeedMidUID



		If ISNULL(@WindSpeedStartUID, '') <> '' and @WindSpeedStart <> '' 
		BEGIN

			IF (select WindSpeed from [dbo].[tgWindSpeed] where WindSpeedUID = @WindSpeedStartUID) <> Cast(@WindSpeedStart as Float)
			BEGIN

				select @NextID = IDENT_CURRENT('tgWindSpeed') + 1

				Select @NewWindSpeedStartUID = [dbo].[CreateUID]('WindSpeed', @NextID, 'WEB', getdate())

				Insert Into tgWindSpeed
				(
					WindSpeedUID,
					InspectionRequestUID,
					CreatedUserUID,
					ModifiedUserUID,
					Comments,
					Revision,
					ActiveFlag,
					WindSpeed,
					MapGridUID
					
				)
				Values(
					@NewWindSpeedStartUID,
					@InspectionRequestUID,
					@SupervisorUID,
					@SupervisorUID,
					'Manually Added From the WEB',
					0,
					1,
					Cast(@WindSpeedStart as Float),
					@MapGridUID
				)

			END

		END
		ELSE If ISNULL(@WindSpeedStartUID, '') = '' and @WindSpeedStart <> '' 
		BEGIN

			select @NextID = IDENT_CURRENT('tgWindSpeed') + 1

			Select @NewWindSpeedStartUID = [dbo].[CreateUID]('WindSpeed', @NextID, 'WEB', getdate())

			Insert Into tgWindSpeed
			(
				WindSpeedUID,
				InspectionRequestUID,
				--ProjectID,
				CreatedUserUID,
				ModifiedUserUID,
				Comments,
				Revision,
				ActiveFlag,
				WindSpeed,
				MapGridUID
					
			)
			Values(
				@NewWindSpeedStartUID,
				@InspectionRequestUID,
				@SupervisorUID,
				@SupervisorUID,
				'Manually Added From the WEB',
				0,
				1,
				Cast(@WindSpeedStart as Float),
				@MapGridUID
			)


		END
		ELSE IF @WindSpeedStart = '' 
		BEGIN

			set @NewWindSpeedStartUID = ''

		END


		IF @WindSpeedMid = 'NA'
		BEGIN
			Set @NewWindSpeedMidUID = ''
		END
		ELSE If ISNULL(@WindSpeedMidUID, '') <> '' and @WindSpeedMid <> '' 
		BEGIN

			IF (select WindSpeed from [dbo].[tgWindSpeed] where WindSpeedUID = @WindSpeedMidUID) <> Cast(@WindSpeedMid as Float)
			BEGIN

				select @NextID = IDENT_CURRENT('tgWindSpeed') + 1

				Select @NewWindSpeedMidUID = [dbo].[CreateUID]('WindSpeed', @NextID, 'WEB', getdate())

				Insert Into tgWindSpeed
				(
					WindSpeedUID,
					InspectionRequestUID,
					--ProjectID,
					CreatedUserUID,
					ModifiedUserUID,
					Comments,
					Revision,
					ActiveFlag,
					WindSpeed,
					MapGridUID
					
				)
				Values(
					@NewWindSpeedMidUID,
					@InspectionRequestUID,
					@SupervisorUID,
					@SupervisorUID,
					'Manually Added From the WEB',
					0,
					1,
					Cast(@WindSpeedMid as Float),
					@MapGridUID
				)

			END

		END
		ELSE If ISNULL(@WindSpeedMidUID, '') = '' and @WindSpeedMid <> '' 
		BEGIN

			select @NextID = IDENT_CURRENT('tgWindSpeed') + 1

			Select @NewWindSpeedMidUID = [dbo].[CreateUID]('WindSpeed', @NextID, 'WEB', getdate())

			Insert Into tgWindSpeed
			(
				WindSpeedUID,
				InspectionRequestUID,
				--ProjectID,
				CreatedUserUID,
				ModifiedUserUID,
				Comments,
				Revision,
				ActiveFlag,
				WindSpeed,
				MapGridUID
					
			)
			Values(
				@NewWindSpeedMidUID,
				@InspectionRequestUID,
				@SupervisorUID,
				@SupervisorUID,
				'Manually Added From the WEB',
				0,
				1,
				Cast(@WindSpeedMid as Float),
				@MapGridUID
			)


		END
		ELSE IF @WindSpeedMid = '' 
		BEGIN

			set @NewWindSpeedMidUID = ''

		END


-- We have checked both Wind Speeds.  Now we can update the Inspection Service Record

-- We have to check to see which table to update.  InspecionService or MapStampPicaro

	IF CHARINDEX('PICMapStamp', @InspectionServiceUID) = 0
	BEGIN

		Update tInspectionService set ActiveFlag = 0 where InspectionServicesUID = @InspectionServiceUID

		Select @Revision = count(*) from tInspectionService where InspectionServicesUID = @InspectionServiceUID

		Insert Into tInspectionService
		(
			InspectionServicesUID,
			MasterLeakLogUID,
			MapGridUID,
			InspectionRequestUID,
			InspectionEquipmentUID,
			SourceID,
			CreatedUserUID,
			ModifiedUserUID,
			SrcDTLT,
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
			InspectionServicesUID,
			MasterLeakLogUID,
			MapGridUID,
			InspectionRequestUID,
			InspectionEquipmentUID,
			'WEB', --SourceID,
			CreatedUserUID, --CreatedUserUID,
			@ApproverUID, --ModifiedUserUID,
			getdate(),
			@Revision, -- Revision,
			1, --ActiveFlag,
			StatusType,
			EquipmentType,
			InstrumentType,
			SerialNumber,
			CalibrationLevel,
			CalibrationVerificationFlag,
			WindSpeedStart,
			WindSpeedEnd,
			CASE 
				WHEN @SurveyType = 'TR' THEN 'T'
				WHEN @SurveyType = 'LISA' THEN 'L'
				WHEN @SurveyType = 'FOV' THEN 'V'
				WHEN @SurveyType = 'GAP' THEN 'G'
				ELSE @SurveyType
			END, -- EquipmentModeType
			@FeetOfMain, -- EstimatedFeet,
			@NumberOfServices, -- EstimatedServices,
			@Hours, -- EstimatedHours,
			1, --ApprovedFlag,
			@ApproverUID, --ApprovedByUserUID,
			@ApprovedDatetime, --ApprovedDTLT,
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
			@SurveyMode, --SurveyMode,
			0, --PlaceHolderFlag,
			@NewWindSpeedStartUID, -- WindSpeedStartUID,
			@NewWindSpeedMidUID, -- WindSpeedMidUID,
			@MapAreaNumber,
			0, --LockedFlag,
			TaskOutUID,
			CreateDateTime
		from tInspectionService where InspectionServicesUID = @InspectionServiceUID and Revision = @Revision - 1

	END
	ELSE
	IF CHARINDEX('PICMapStamp', @InspectionServiceUID) > 0
	BEGIN

		IF (Select Count(*) 
			from tMapStampPicaro 
			where InspectionRequestUID = @InspectionRequestUID and Seq < 3 and ActiveFlag = 1) > 0
		BEGIN

			Update tMapStampPicaro
			Set ActiveFlag= 0
			where InspectionRequestUID = @InspectionRequestUID and Seq < 3 and ActiveFlag = 1 
		
			Insert Into tMapStampPicaro
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
				LockedFlag,
				Hours,
				TaskOutUID,
				WindSpeedStartUID,
				WindSpeedMidUID
			)
			Select
				msp.MapStampPicaroUID,
				msp.InspectionRequestUID,
				msp.MapStampUID,
				msp.ProjectID,
				msp.CreatedByUserUID,
				@ApproverUID, --msp.ModifiedByUserUID,
				msp.CreatedDateTime,
				getdate(), --msp.ModifiedDateTime,
				NextRev.NextRevision, -- msp.Revision,
				1, --msp.ActiveFlag,
				@PicaroEquipmentID, -- msp.PicaroEquipmentID,
				0, -- msp.FeetOfMain,
				0, -- msp.Services,
				msp.WindSpeedStart,
				msp.WindSpeedMid,
				msp.StatusType,
				@SurveyorUID, -- msp.SurveyorUID,
				@DateSurveyed, -- msp.SurveyDate,
				msp.Seq,
				msp.LockedFlag,
				0, --msp.Hours,
				msp.TaskOutUID,
				msp.WindSpeedStartUID,
				msp.WindSpeedMidUID
			From tMapStampPicaro msp
			Join (select MapStampPicaroUID, Count(*) NextRevision from [dbo].[tMapStampPicaro] 
					where InspectionRequestUID = @InspectionRequestUID --and Seq < 3
					Group By MapStampPicaroUID) NextRev on msp.MapStampPicaroUID = NextRev.MapStampPicaroUID and msp.Revision = NextRev.NextRevision - 1
		
		
		END
		ELSE
		BEGIN

			Update tMapStampPicaro set ActiveFlag = 0 where MapStampPicaroUID = @InspectionServiceUID

			Select @Revision = count(*) from tMapStampPicaro where MapStampPicaroUID = @InspectionServiceUID

			Insert Into tMapStampPicaro
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
				LockedFlag,
				Hours,
				TaskOutUID,
				WindSpeedStartUID,
				WindSpeedMidUID
			)
			Select
				msp.MapStampPicaroUID,
				msp.InspectionRequestUID,
				msp.MapStampUID,
				msp.ProjectID,
				msp.CreatedByUserUID,
				@ApproverUID, --msp.ModifiedByUserUID,
				msp.CreatedDateTime,
				getdate(), --msp.ModifiedDateTime,
				@Revision, -- msp.Revision,
				1, --msp.ActiveFlag,
				@PicaroEquipmentID, -- msp.PicaroEquipmentID,
				@FeetOfMain, -- msp.FeetOfMain,
				@NumberOfServices, -- msp.Services,
				msp.WindSpeedStart,
				msp.WindSpeedMid,
				msp.StatusType,
				@SurveyorUID, -- msp.SurveyorUID,
				@DateSurveyed, -- msp.SurveyDate,
				msp.Seq,
				msp.LockedFlag,
				@Hours, -- msp.Hours,
				msp.TaskOutUID,
				@WindSpeedStartUID,
				@WindSpeedMidUID
			From tMapStampPicaro msp
			Where MapStampPicaroUID = @InspectionServiceUID and Revision = @Revision - 1

		END

	END

	Commit Transaction

End Try
Begin Catch
	
	Rollback Transaction
	set @ReturnVal = 0

End Catch

/*******************************************************

   Last thing we do
	Drop the table created in this proceedure

******************************************************/

Drop Table #JSON_Parse

SET NOCOUNT OFF
	
Select @ReturnVal as Succeeded