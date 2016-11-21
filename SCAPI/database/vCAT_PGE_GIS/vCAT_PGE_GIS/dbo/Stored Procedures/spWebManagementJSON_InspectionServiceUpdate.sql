






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


		Select @InspectionServiceUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'InspectionServicesUID'), '')
		Select @SurveyMode = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SurveyMode'), '')
		Select @WindSpeedStart = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WindSpeedStart'), '')
		Select @WindSpeedMid = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WindSpeedMid'), '')
		Select @FeetOfMain = ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMain'), '')
		Select @NumberOfServices = ISNULL((Select StringValue From #JSON_Parse Where Name = 'NumberOfServices'), '')
		Select @Hours = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Hours'), '')
		Select @Date = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Date'), '')
		Select @SurveyorLANID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'UserLANID'), '')
		Select @SupervisorUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'UserLANID'), '')

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
		@SupervisorUID, --CreatedUserUID,
		@SupervisorUID, --ModifiedUserUID,
		@Revision, -- Revision,
		1, --ActiveFlag,
		'In Progress', --StatusType,
		EquipmentType,
		InstrumentType,
		SerialNumber,
		CalibrationLevel,
		CalibrationVerificationFlag,
		WindSpeedStart,
		WindSpeedEnd,
		EquipmentModeType,
		@FeetOfMain, -- EstimatedFeet,
		@NumberOfServices, -- EstimatedServices,
		@Hours, -- EstimatedHours,
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
		0, --PlaceHolderFlag,
		@NewWindSpeedStartUID, -- WindSpeedStartUID,
		@NewWindSpeedMidUID, -- WindSpeedMidUID,
		MapAreaNumber,
		0, --LockedFlag,
		TaskOutUID,
		CreateDateTime
	from tInspectionService where InspectionServicesUID = @InspectionServiceUID and Revision = @Revision - 1


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