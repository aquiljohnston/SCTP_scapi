


CREATE PROCEDURE [dbo].[spJSON_TaskOut]
(
      @JSON_Str VarChar(Max)
    
)
AS 

-- This First Part should be in all SP userd to process a JSON record.  
-- It is designed to archive the original data

	SET NOCOUNT ON
	
	Declare @ClientID varchar(10)
		,@UserUID varchar(100)
		,@TransactionType VarChar(20)
		,@SQLQuery varchar(max)
		,@SingleQuote char(1) = CHAR(39)
		

	--Set @SingleQuote = CHAR(39)

	Set @TransactionType = 'TaskOut'

	
	
		Select * Into #JSON_Parse From [dbo].[parseJSON](@JSON_Str)
	
		Update #JSON_Parse Set StringValue = 0 where charindex('flag', name) > 0 and StringValue = 'null'
		Update #JSON_Parse Set StringValue = 0 where ValueType = 'boolean' and StringValue = 'false'
		Update #JSON_Parse Set StringValue = 1 where ValueType = 'boolean' and StringValue = 'true'
		Update #JSON_Parse Set StringValue = '' where StringValue = 'null' or StringValue = 'Please Make A Selection'
		Update #JSON_Parse Set StringValue = '19000101000000' Where Name In ('SrcDTLT', 'SrcDTGMT', 'SrcOpenDTLT', 'SrcClosedDTLT') and StringValue = ''
	
	
		Select @ClientID = 1
		--Select @ClientID = StringValue From #JSON_Parse Where Name = 'ClientID'
		Select Top 1 @UserUID = StringValue From #JSON_Parse Where Name = 'ActivityCreatedUserUID'

		Insert Into [dbo].[tTabletDataInsertArchive] (
			CreatedUserUID
			,TransactionType
			,InsertedData
			)
		Values (
			@UserUID
			,'InspectionServices'
			,@JSON_Str
			)

/***************************************************************************

--  Create the unique function needed for the record type below this line

***************************************************************************/

		Declare 
			@InspectionServiceUID varchar(100) 
			,@InspectionRequestUID varchar(100) 
			,@MapGridUID varchar(100)
			,@SourceID varchar(25) 
			,@NextID int
			,@CreatedUserUID varchar(100)
			,@ModifiedUserUID varchar(100) 
			,@CreatedDate datetime 
			,@ModifiedDate datetime 
			,@SrcDTLT datetime 
			,@SrcOpenDTLT datetime 
			,@SrcClosedDTLT datetime 
			,@Comments varchar(500) 
			,@StatusType varchar(50) 
			,@Revision int 
			,@ActiveFlag bit = 1
			,@CompleteFlag bit = 0
			,@TaskOutMapsObjectID int
			,@ProcessingObjectID int
			,@WorkQueueObjectID int
			,@ActivityUID varchar(100)
			,@MasterLeakLogUID	varchar(100)
			,@SurveyFreq varchar(20)
			,@WorkCenter varchar(50)
			,@DispatchMethod varchar(50)
			,@Division varchar(50)
			,@AssignedUserUID varchar(100)
			,@Status varchar(50)
			,@TotalEnteredTime time
			,@WindSpeedStartUID varchar(100)
			,@WindSpeedMidUID varchar(100)
			,@EquipmentUID varchar(100)
			--,@GradedByUID varchar(100)
			,@TRFeetOfMainFoot Float
			,@TRFeetOfMainMobile float
			,@TRNumberOfServices int
			,@FootHours float
			,@MobileHours float
			,@PreFootHours Varchar(20)
			,@PreMobileHours Varchar(20)
			,@NotificationID varchar(25)
			,@LANID varchar(20)
			--,@srcDTLT datetime
			,@CurrentInspectionType varchar(200)
			,@PICCount int
			,@InsertedPIC int = 0
			,@PICUID varchar(100)
			--,@MapPlat varchar(20)
			,@INFTaskOutUID varchar(200) = 'TASKOUT_'
			,@INFTaskOutDateTime varchar(20) = Format(getdate(), 'yyyyMMddhhmmss', 'en-US')
			--,@NextID int
			,@EquipmentSerNo varchar(20)
			,@EquipmentType varchar(20)
			--,@SurveyFreq varchar(20)
			,@InspectionServicePendingStatusType varchar(20) = 'Pending'



			,@IsTraditional bit
			,@IsFoot bit
			,@IsMobile bit
			
			
			
			
			,@IsPicarro bit

			,@IsFOV bit
			,@FOVFeetOfMainFoot float
			,@FOVFeetOfMainMoble float
			,@FOVNumberOfServices int
			,@PreFOVHours varchar(20)
			,@FOVHours Float
			
			,@IsLisa bit
			,@IsLisaFoot bit
			,@IsLisaMobile bit
			,@LisaFeetOfMainFoot float
			,@LisaFeetOfMainMoble float
			,@LisaNumberOfServices int
			,@PreLisaHours varchar(20)
			,@LisaHours Float
			
			,@IsGap bit
			,@IsGapFoot bit
			,@IsGapMobile bit
			,@GapFeetOfMainFoot float
			,@GapFeetOfMainMoble float
			,@GapNumberOfServices int
			,@PreGapHours varchar(20)
			,@GapHours Float
			
			,@IsDeleted bit


			,@MapPlat varchar(25)
			,@Instrument varchar(25)
			,@SerialNumber varchar(25)
			,@TaskOutUID varchar(100)
			,@AreaNumber varchar(50)

			,@PlaceHolderPassNo int = 1
			,@IndicationPassNo int = 1
			,@HoldInspectionServiceUID varchar(100) 
			,@InProgressIndictionUID varchar(100)
			,@UIDSufixTR_Foot varchar(25) = '_TR_Foot'
			,@UIDSufixTR_Mobile varchar(25) = '_TR_Mobile'
			,@UIDSufixPIC_FOV_Foot varchar(25) = '_PIC_FOV_Foot'
			,@UIDSufixPIC_FOV_Mobile varchar(25) = '_PIC_FOV_Mobile'
			,@UIDSufixPIC_LISA_Foot varchar(25) = '_PIC_LISA_Foot'
			,@UIDSufixPIC_LISA_Mobile varchar(25) = '_PIC_LISA_Mobile'
			,@UIDSufixPIC_GAP_Foot varchar(25) = '_PIC_GAP_Foot'
			,@UIDSufixPIC_GAP_Mobile varchar(25) = '_PIC_GAP_Mobile'

			Select Top 1 @ActivityUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'ActivityUID'), 0)
			--Select Top 1 @SrcDTLT = ISNULL((Select StringValue From #JSON_Parse Where Name = 'ActivitySrcDTLT'), 0)
			Select @TaskOutMapsObjectID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'TaskOutMaps'), 0)

			Declare objProcessingObj Cursor For
			Select Object_ID From #JSON_Parse Where parent_ID = @TaskOutMapsObjectID

			Open objProcessingObj

			Fetch Next From objProcessingObj Into @ProcessingObjectID

			While @@FETCH_STATUS = 0
			BEGIN  --1
				
				--Select @InspectionRequestUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'InspectionRequestUID' and Parent_ID = @ProcessingObjectID), '')
				Select @MasterLeakLogUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'MasterLeakLogUID' and Parent_ID = @ProcessingObjectID), '') 
				Select @MapGridUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'MapGridUID' and Parent_ID = @ProcessingObjectID), '') 
				Select @SurveyFreq = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SurveyType' and Parent_ID = @ProcessingObjectID), '')
				Select @WorkCenter = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WorkCenter' and Parent_ID = @ProcessingObjectID), '')
				Select @DispatchMethod = ISNULL((Select StringValue From #JSON_Parse Where Name = 'DispatchMethod' and Parent_ID = @ProcessingObjectID), '')
				Select @Division = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Division' and Parent_ID = @ProcessingObjectID), '')
				Select @Status = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Status' and Parent_ID = @ProcessingObjectID), '')
				Select @TotalEnteredTime = CAST(ISNULL((Select StringValue From #JSON_Parse Where Name = 'TotalEnteredTime' and Parent_ID = @ProcessingObjectID), '00:00') as Time)
				Select @WindSpeedStartUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WindSpeedStartUID' and Parent_ID = @ProcessingObjectID), '')
				Select @WindSpeedMidUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WindSpeedMidUID' and Parent_ID = @ProcessingObjectID), '')
				Select @EquipmentUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'InspectionEquipmentUID' and Parent_ID = @ProcessingObjectID), '')
				Select @TaskOutUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'TaskOutTabletUID' and Parent_ID = @ProcessingObjectID), '')
				Select @AreaNumber = ISNULL((Select StringValue From #JSON_Parse Where Name = 'AreaNumber' and Parent_ID = @ProcessingObjectID), '')
				Select @InspectionRequestUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'AssignedInspectionRequestUID' and Parent_ID = @ProcessingObjectID), '') 
				Select @EquipmentSerNo = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SerialNumber' and Parent_ID = @ProcessingObjectID), '')
				Select @EquipmentType = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Instrument' and Parent_ID = @ProcessingObjectID), '')




				Select @PreFootHours  = ISNULL((Select StringValue From #JSON_Parse Where Name = 'HoursFoot' and Parent_ID = @ProcessingObjectID),'0')
				Select @PreMobileHours = ISNULL((Select StringValue From #JSON_Parse Where Name = 'HoursMobile' and Parent_ID = @ProcessingObjectID), '0')


				--Select @srcDTLT = dbo.JSON_ParseDate_Str(CASE WHEN ISNULL((Select StringValue From #JSON_Parse Where Name = 'SrcDTLT'), '19000101000000') = 'null' THEN '19000101000000' ELSE ISNULL((Select StringValue From #JSON_Parse Where Name = 'SrcOpenDTLT'), '19000101000000') END)
				Select @srcDTLT = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SrcDTLT' and Parent_ID = @ProcessingObjectID), '19000101000000') 

				
				Select @SourceID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'SourceID' and Parent_ID = @ProcessingObjectID), '')
			
				Select @IsFoot  = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isFoot' and Parent_ID = @ProcessingObjectID), 0)
				Select @IsMobile = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isMobile' and Parent_ID = @ProcessingObjectID), 0)
				
				
				Select @IsTraditional = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isTraditional' and Parent_ID = @ProcessingObjectID), 0)
				Select @TRFeetOfMainFoot = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainFoot' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @TRFeetOfMainMobile = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainMobile' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @TRNumberOfServices = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'NumberOfServicesFoot' and Parent_ID = @ProcessingObjectID), 0) as float)
				
				Select @IsPicarro = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isPicarro' and Parent_ID = @ProcessingObjectID), 0)
				
				Select @IsFOV = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isFOV' and Parent_ID = @ProcessingObjectID), 0)
				Select @FOVFeetOfMainFoot = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainFootFOV' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @FOVFeetOfMainMoble = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainMobileFOV' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @FOVNumberOfServices = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'NumberOfServicesFOV' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @PreFOVHours = ISNULL((Select StringValue From #JSON_Parse Where Name = 'HoursFOV' and Parent_ID = @ProcessingObjectID), '0') 
				
				Select @IsLisa = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isLISA' and Parent_ID = @ProcessingObjectID), 0)
				Select @IsLisaFoot = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isLISAFoot' and Parent_ID = @ProcessingObjectID), 0)
				Select @IsLisaMobile = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isLISAMobile' and Parent_ID = @ProcessingObjectID), 0)
				Select @LisaFeetOfMainFoot = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainFootLisa' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @LisaFeetOfMainMoble = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainMobileLisa' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @LisaNumberOfServices = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'NumberOfServicesLisa' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @PreLisaHours = ISNULL((Select StringValue From #JSON_Parse Where Name = 'HoursLisa' and Parent_ID = @ProcessingObjectID), '0')
				
				Select @IsGap = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isGAP' and Parent_ID = @ProcessingObjectID), 0)
				Select @IsGapFoot = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isGAPFoot' and Parent_ID = @ProcessingObjectID), 0)
				Select @IsGapMobile = ISNULL((Select StringValue From #JSON_Parse Where Name = 'isGAPMobile' and Parent_ID = @ProcessingObjectID), 0)
				Select @GapFeetOfMainFoot = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainFootGap' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @GapFeetOfMainMoble = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'FeetOfMainMobileGap' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @GapNumberOfServices = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'NumberOfServicesGap' and Parent_ID = @ProcessingObjectID), 0) as float)
				Select @PreGapHours = ISNULL((Select StringValue From #JSON_Parse Where Name = 'HoursGap' and Parent_ID = @ProcessingObjectID), '0')

				Select @WorkQueueObjectID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'WorkQueuee' and Parent_ID = @ProcessingObjectID), 0)
				
				--Select @InspectionRequestUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'IRUID' and Parent_ID = @WorkQueueObjectID), '')
				Select @AssignedUserUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'AssignedUserUID' and Parent_ID = @WorkQueueObjectID), '')
				Select @NotificationID = Cast(ISNULL((Select StringValue From #JSON_Parse Where Name = 'NotificationID' and Parent_ID = @WorkQueueObjectID), '') as Float)

				Select @LANID = UserLANID from UserTb where UserUID = @AssignedUserUID

--PreFootHours
				
				If CHARINDEX(':', @PreFootHours) > 0 AND ISNUMERIC(Replace(@PreFootHours, ':', '')) = 1
				Begin

					SELECT @FootHours =	Case WHEN CHARINDEX(':', @PreFootHours) > 1 THEN Cast(Left(@PreFootHours, CHARINDEX(':', @PreFootHours) - 1) as Float) ELSE 0 END 
						+ Case WHEN CHARINDEX(':', Reverse(@PreFootHours)) > 1 THEN ((Cast(Right(@PreFootHours, CHARINDEX(':', Reverse(@PreFootHours)) - 1) as float)) / 60) ELSE 0 END

				END
				ELSE
				IF CHARINDEX('.', @PreFootHours) > 0 AND ISNUMERIC(Replace(@PreFootHours, '.', '')) = 1
				BEGIN

					SELECT @FootHours =	Cast(@PreFootHours as Float)

				END
				ELSE
				BEGIN

					SELECT @FootHours =	0

				END


--PreMobileHours

				If CHARINDEX(':', @PreMobileHours) > 0 AND ISNUMERIC(Replace(@PreMobileHours, ':', '')) = 1
				Begin

					SELECT @MobileHours =	Case WHEN CHARINDEX(':', @PreMobileHours) > 1 THEN Cast(Left(@PreMobileHours, CHARINDEX(':', @PreMobileHours) - 1) as Float) ELSE 0 END 
						+ Case WHEN CHARINDEX(':', Reverse(@PreMobileHours)) > 1 THEN ((Cast(Right(@PreMobileHours, CHARINDEX(':', Reverse(@PreMobileHours)) - 1) as float)) / 60) ELSE 0 END

				END
				ELSE
				IF CHARINDEX('.', @PreMobileHours) > 0 AND ISNUMERIC(Replace(@PreMobileHours, '.', '')) = 1
				BEGIN

					SELECT @MobileHours =	Cast(@PreMobileHours as Float)

				END
				ELSE
				BEGIN

					SELECT @MobileHours = 0

				END


--PreFOVHours
				If CHARINDEX(':', @PreFOVHours) > 0 AND ISNUMERIC(Replace(@PreFOVHours, ':', '')) = 1
				Begin

					SELECT @FOVHours =	Case WHEN CHARINDEX(':', @PreFOVHours) > 1 THEN Cast(Left(@PreFOVHours, CHARINDEX(':', @PreFOVHours) - 1) as Float) ELSE 0 END 
						+ Case WHEN CHARINDEX(':', Reverse(@PreFOVHours)) > 1 THEN ((Cast(Right(@PreFOVHours, CHARINDEX(':', Reverse(@PreFOVHours)) - 1) as float)) / 60) ELSE 0 END

				END
				ELSE
				IF CHARINDEX('.', @PreFOVHours) > 0 AND ISNUMERIC(Replace(@PreFOVHours, '.', '')) = 1
				BEGIN

					SELECT @FOVHours =	Cast(@PreFOVHours as Float)

				END
				ELSE
				BEGIN

					SELECT @FOVHours =	0

				END


--PreLisaHours

				If CHARINDEX(':', @PreLisaHours) > 0 AND ISNUMERIC(Replace(@PreLisaHours, ':', '')) = 1
				Begin

					SELECT @LisaHours =	Case WHEN CHARINDEX(':', @PreLisaHours) > 1 THEN Cast(Left(@PreLisaHours, CHARINDEX(':', @PreLisaHours) - 1) as Float) ELSE 0 END 
						+ Case WHEN CHARINDEX(':', Reverse(@PreLisaHours)) > 1 THEN ((Cast(Right(@PreLisaHours, CHARINDEX(':', Reverse(@PreLisaHours)) - 1) as float)) / 60) ELSE 0 END

				END
				ELSE
				IF CHARINDEX('.', @PreLisaHours) > 0 AND ISNUMERIC(Replace(@PreLisaHours, '.', '')) = 1
				BEGIN

					SELECT @LisaHours =	Cast(@PreLisaHours as Float)

				END
				ELSE
				BEGIN

					SELECT @LisaHours =	0

				END

--PreGapHours

				If CHARINDEX(':', @PreGapHours) > 0 AND ISNUMERIC(Replace(@PreGapHours, ':', '')) = 1
				Begin

					SELECT @GapHours =	Case WHEN CHARINDEX(':', @PreGapHours) > 1 THEN Cast(Left(@PreGapHours, CHARINDEX(':', @PreGapHours) - 1) as Float) ELSE 0 END 
						+ Case WHEN CHARINDEX(':', Reverse(@PreGapHours)) > 1 THEN ((Cast(Right(@PreGapHours, CHARINDEX(':', Reverse(@PreGapHours)) - 1) as float)) / 60) ELSE 0 END

				END
				ELSE
				IF CHARINDEX('.', @PreGapHours) > 0 AND ISNUMERIC(Replace(@PreGapHours, '.', '')) = 1
				BEGIN

					SELECT @GapHours =	Cast(@PreGapHours as Float)

				END
				ELSE
				BEGIN

					SELECT @GapHours =	0

				END
				
--Marked Deleted all Place Holder Service Records

				IF @PlaceHolderPassNo = 1 
				BEGIN

					Declare PlaceHolder Cursor For
					Select InspectionServicesUID 
					from [dbo].[tInspectionService] 
					where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1 and PlaceHolderFlag = 1 and StatusType <> 'Deleted'

					Open PlaceHolder

					Fetch Next From PlaceHolder into @HoldInspectionServiceUID

					While @@Fetch_Status = 0
					BEGIN

						Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @HoldInspectionServiceUID

						Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @HoldInspectionServiceUID

						
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
							SrcDTLT, 
							--SrvDTLT, 
							--SrvDTLTOffset, 
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
							PlaceHolderFlag
						)
						Select
							InspectionServicesUID, 
							MasterLeakLogUID, 
							MapGridUID, 
							InspectionRequestUID, 
							InspectionEquipmentUID, 
							ProjectID, 
							SourceID, 
							CreatedUserUID, 
							@UserUID, --ModifiedUserUID, 
							@SrcDTLT, --SrcDTLT, 
							--SrvDTLT, 
							--SrvDTLTOffset, 
							Comments, 
							'Marked Deleted Due to task out', --RevisionComments, 
							Revision, 
							1, --ActiveFlag, 
							'Deleted', --StatusType, 
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
							PlaceHolderFlag
						From [dbo].[tInspectionService] Where InspectionServicesUID = @HoldInspectionServiceUID and Revision = @Revision - 1


						Fetch Next From PlaceHolder into @HoldInspectionServiceUID

					END

					Set @PlaceHolderPassNo = 2
								
				END

--Marked Any Indications that are still In Progress as Pending

				IF @IndicationPassNo = 1 
				BEGIN

					Declare InProgress Cursor For
					Select Distinct AssetAddressIndicationUID
					from [dbo].[tgAssetAddressIndication]
					where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1 and StatusType in ('InProgress', 'In Progress')

					Open InProgress

					Fetch Next From InProgress into @InProgressIndictionUID

					While @@FETCH_STATUS = 0
					BEGIN

						Select @Revision = Count(*) from [dbo].[tgAssetAddressIndication] Where AssetAddressIndicationUID = @InProgressIndictionUID

						Update [dbo].[tgAssetAddressIndication] set ActiveFlag = 0 Where AssetAddressIndicationUID = @InProgressIndictionUID

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
							SrcDTLT,
							SrvDTLT,
							SrvDTLTOffset,
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
							LockedFlag
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
							ModifiedUserUID,
							SrcDTLT,
							SrvDTLT,
							SrvDTLTOffset,
							SrcOpenDTLT,
							SrcClosedDTLT,
							GPSType,
							GPSSentence,
							Latitude,
							Longitude,
							SHAPE,
							Comments,
							RevisionComments,
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
							0 --LockedFlag
							
						From [dbo].[tgAssetAddressIndication] Where AssetAddressIndicationUID = @InProgressIndictionUID and Revision = @Revision - 1

						Fetch Next From InProgress into @InProgressIndictionUID

					END

					Set @IndicationPassNo = 2
								
				END

--Set the Base INF TaskoutUID

				--Select @EquipmentSerNo = SerialNumber, @EquipmentType = EquipmentType from [dbo].[tInspectionsEquipment] where InspecitonEquipmentUID = @EquipmentUID

				Set @INFTaskOutUID = @INFTaskOutUID + @SourceID + '_' + @EquipmentSerNo + '_' 
				

				If @IsTraditional = 1 AND @IsFoot = 1
				BEGIN
				
					
					Set @INFTaskOutUID = @INFTaskOutUID + 'TR_F' + @INFTaskOutDateTime + Right(@TaskOutUID, CHARINDEX('_', Reverse(@TaskOutUID)))
					
					Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @TaskOutUID + @UIDSufixTR_Foot

					Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @TaskOutUID  + @UIDSufixTR_Foot

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
						SrcDTLT,
						Revision,
						ActiveFlag,
						StatusType,
						WindSpeedStartUID,
						WindSpeedMidUID,
						EquipmentModeType,
						EstimatedFeet,
						EstimatedServices,
						EstimatedHours,
						SurveyMode,
						EquipmentType,
						SerialNumber,
						TaskOutUID,
						CreateDateTime
						
					)
					Values
					(
						 @TaskOutUID + @UIDSufixTR_Foot --InspectionServicesUID
						,@MasterLeakLogUID	-- MasterLeakLogUID
						,@MapGridUID
						,@InspectionRequestUID
						,@EquipmentUID
						,1 --ProjectID
						,@SourceID
						,@UserUID
						,@UserUID
						,@SrcDTLT
						,@Revision
						,1 --ActiveFlag
						,@InspectionServicePendingStatusType --StatusType
						,@WindSpeedStartUID
						,@WindSpeedMidUID
						,'TR' --EquipmentModeType
						,@TRFeetOfMainFoot
						,@TRNumberOfServices
						,@FootHours
						,'F' --SurveyMode
						,@EquipmentType
						,@EquipmentSerNo
						,@INFTaskOutUID
						,@SrcDTLT
					)

				END


				IF @IsTraditional = 1 AND @IsMobile = 1
				BEGIN

					
					Set @INFTaskOutUID = @INFTaskOutUID + 'TR_M' + @INFTaskOutDateTime + Right(@TaskOutUID, CHARINDEX('_', Reverse(@TaskOutUID)))
					
					Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @TaskOutUID + @UIDSufixTR_Mobile

					Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @TaskOutUID  + @UIDSufixTR_Mobile

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
						SrcDTLT,
						Revision,
						ActiveFlag,
						StatusType,
						WindSpeedStartUID,
						WindSpeedMidUID,
						EquipmentModeType,
						EstimatedFeet,
						EstimatedServices,
						EstimatedHours,
						SurveyMode,
						EquipmentType,
						SerialNumber,
						TaskOutUID,
						CreateDateTime
					)
					Values
					(
						 @TaskOutUID + @UIDSufixTR_Mobile --InspectionServicesUID
						,@MasterLeakLogUID	-- MasterLeakLogUID
						,@MapGridUID
						,@InspectionRequestUID
						,@EquipmentUID
						,1 --ProjectID
						,@SourceID
						,@UserUID
						,@UserUID
						,@SrcDTLT
						,@Revision
						,1 --ActiveFlag
						,@InspectionServicePendingStatusType --StatusType
						,@WindSpeedStartUID
						,@WindSpeedMidUID
						,'TR' --EquipmentModeType
						,@TRFeetOfMainMobile
						,0 --EstimatedService
						,@MobileHours
						,'M' --SurveyMode
						,@EquipmentType
						,@EquipmentSerNo
						,@INFTaskOutUID
						,@SrcDTLT
					)


				END

				IF @IsPicarro = 1 AND @IsFOV = 1 
				BEGIN

					
					Set @INFTaskOutUID = @INFTaskOutUID + 'PIC_FOV' + @INFTaskOutDateTime + Right(@TaskOutUID, CHARINDEX('_', Reverse(@TaskOutUID)))
					
					Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @TaskOutUID + @UIDSufixPIC_FOV_Foot

					Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @TaskOutUID  + @UIDSufixPIC_FOV_Foot

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
						SrcDTLT,
						Revision,
						ActiveFlag,
						StatusType,
						WindSpeedStartUID,
						WindSpeedMidUID,
						EquipmentModeType,
						EstimatedFeet,
						EstimatedServices,
						EstimatedHours,
						SurveyMode,
						EquipmentType,
						SerialNumber,
						TaskOutUID,
						CreateDateTime
					)
					Values
					(
						 @TaskOutUID + @UIDSufixPIC_FOV_Foot --InspectionServicesUID
						,@MasterLeakLogUID	-- MasterLeakLogUID
						,@MapGridUID
						,@InspectionRequestUID
						,@EquipmentUID
						,1 --ProjectID
						,@SourceID
						,@UserUID
						,@UserUID
						,@SrcDTLT
						,@Revision
						,1 --ActiveFlag
						,@InspectionServicePendingStatusType --StatusType
						,@WindSpeedStartUID
						,@WindSpeedMidUID
						,'PIC_FOV' --EquipmentModeType
						,@FOVFeetOfMainFoot
						,@FOVNumberOfServices
						,@FOVHours
						,'F' --SurveyMode
						,@EquipmentType
						,@EquipmentSerNo
						,@INFTaskOutUID
						,@SrcDTLT
					)
					
				END

				IF @IsPicarro = 1 AND @IsLisa = 1 and @IsLisaFoot = 1
				BEGIN

					Set @INFTaskOutUID = @INFTaskOutUID + 'PIC_LISA_F' + @INFTaskOutDateTime + Right(@TaskOutUID, CHARINDEX('_', Reverse(@TaskOutUID)))
					
					Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @TaskOutUID + @UIDSufixPIC_LISA_Foot

					Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @TaskOutUID  + @UIDSufixPIC_LISA_Foot

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
						SrcDTLT,
						Revision,
						ActiveFlag,
						StatusType,
						WindSpeedStartUID,
						WindSpeedMidUID,
						EquipmentModeType,
						EstimatedFeet,
						EstimatedServices,
						EstimatedHours,
						SurveyMode,
						EquipmentType,
						SerialNumber,
						TaskOutUID,
						CreateDateTime
					)
					Values
					(
						 @TaskOutUID + @UIDSufixPIC_LISA_Foot --InspectionServicesUID
						,@MasterLeakLogUID	-- MasterLeakLogUID
						,@MapGridUID
						,@InspectionRequestUID
						,@EquipmentUID
						,1 --ProjectID
						,@SourceID
						,@UserUID
						,@UserUID
						,@SrcDTLT
						,@Revision
						,1 --ActiveFlag
						,@InspectionServicePendingStatusType --StatusType
						,@WindSpeedStartUID
						,@WindSpeedMidUID
						,'PIC_LISA_Foot' --EquipmentModeType
						,@LisaFeetOfMainFoot
						,@LisaNumberOfServices
						,@LisaHours
						,'F' --SurveyMode
						,@EquipmentType
						,@EquipmentSerNo
						,@INFTaskOutUID
						,@SrcDTLT
					)
					
				END


				IF @IsPicarro = 1 AND @IsLisa = 1 and @IsLisaMobile = 1
				BEGIN

					Set @INFTaskOutUID = @INFTaskOutUID + 'PIC_LISA_M' + @INFTaskOutDateTime + Right(@TaskOutUID, CHARINDEX('_', Reverse(@TaskOutUID)))
					
					
					Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @TaskOutUID + @UIDSufixPIC_LISA_Mobile

					Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @TaskOutUID  + @UIDSufixPIC_LISA_Mobile

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
						SrcDTLT,
						Revision,
						ActiveFlag,
						StatusType,
						WindSpeedStartUID,
						WindSpeedMidUID,
						EquipmentModeType,
						EstimatedFeet,
						EstimatedServices,
						EstimatedHours,
						SurveyMode,
						EquipmentType,
						SerialNumber,
						TaskOutUID,
						CreateDateTime
					)
					Values
					(
						 @TaskOutUID + @UIDSufixPIC_LISA_Mobile --InspectionServicesUID
						,@MasterLeakLogUID	-- MasterLeakLogUID
						,@MapGridUID
						,@InspectionRequestUID
						,@EquipmentUID
						,1 --ProjectID
						,@SourceID
						,@UserUID
						,@UserUID
						,@SrcDTLT
						,@Revision
						,1 --ActiveFlag
						,@InspectionServicePendingStatusType --StatusType
						,@WindSpeedStartUID
						,@WindSpeedMidUID
						,'PIC_LISA_Mobile' --EquipmentModeType
						,@LisaFeetOfMainMoble
						,0
						,@LisaHours
						,'M' --SurveyMode
						,@EquipmentType
						,@EquipmentSerNo
						,@INFTaskOutUID
						,@SrcDTLT
					)
					
				END

				IF @IsPicarro = 1 AND @IsGap = 1 and @IsGapFoot = 1
				BEGIN

					Set @INFTaskOutUID = @INFTaskOutUID + 'PIC_GAP_F' + @INFTaskOutDateTime + Right(@TaskOutUID, CHARINDEX('_', Reverse(@TaskOutUID)))
					

					Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @TaskOutUID + @UIDSufixPIC_GAP_Foot

					Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @TaskOutUID  + @UIDSufixPIC_GAP_Foot

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
						SrcDTLT,
						Revision,
						ActiveFlag,
						StatusType,
						WindSpeedStartUID,
						WindSpeedMidUID,
						EquipmentModeType,
						EstimatedFeet,
						EstimatedServices,
						EstimatedHours,
						SurveyMode,
						EquipmentType,
						SerialNumber,
						TaskOutUID,
						CreateDateTime
					)
					Values
					(
						 @TaskOutUID + @UIDSufixPIC_GAP_Foot --InspectionServicesUID
						,@MasterLeakLogUID	-- MasterLeakLogUID
						,@MapGridUID
						,@InspectionRequestUID
						,@EquipmentUID
						,1 --ProjectID
						,@SourceID
						,@UserUID
						,@UserUID
						,@SrcDTLT
						,@Revision
						,1 --ActiveFlag
						,@InspectionServicePendingStatusType --StatusType
						,@WindSpeedStartUID
						,@WindSpeedMidUID
						,'PIC_GAP_Foot' --EquipmentModeType
						,@GapFeetOfMainFoot
						,@GapNumberOfServices
						,@GapHours
						,'F' --SurveyMode
						,@EquipmentType
						,@EquipmentSerNo
						,@INFTaskOutUID
						,@SrcDTLT
					)
					
				END

				IF @IsPicarro = 1 AND @IsGap = 1 and @IsGapMobile = 1
				BEGIN

					Set @INFTaskOutUID = @INFTaskOutUID + 'PIC_GAP_M' + @INFTaskOutDateTime + Right(@TaskOutUID, CHARINDEX('_', Reverse(@TaskOutUID)))
					
					
					Select @Revision = Count(*) from  [dbo].[tInspectionService] Where InspectionServicesUID = @TaskOutUID + @UIDSufixPIC_GAP_Mobile

					Update [dbo].[tInspectionService] set ActiveFlag = 0 Where InspectionServicesUID = @TaskOutUID  + @UIDSufixPIC_GAP_Mobile

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
						SrcDTLT,
						Revision,
						ActiveFlag,
						StatusType,
						WindSpeedStartUID,
						WindSpeedMidUID,
						EquipmentModeType,
						EstimatedFeet,
						EstimatedServices,
						EstimatedHours,
						SurveyMode,
						EquipmentType,
						SerialNumber,
						TaskOutUID,
						CreateDateTime
					)
					Values
					(
						 @TaskOutUID + @UIDSufixPIC_GAP_Mobile --InspectionServicesUID
						,@MasterLeakLogUID	-- MasterLeakLogUID
						,@MapGridUID
						,@InspectionRequestUID
						,@EquipmentUID
						,1 --ProjectID
						,@SourceID
						,@UserUID
						,@UserUID
						,@SrcDTLT
						,@Revision
						,1 --ActiveFlag
						,@InspectionServicePendingStatusType --StatusType
						,@WindSpeedStartUID
						,@WindSpeedMidUID
						,'PIC_GAP_Mobile' --EquipmentModeType
						,@GapFeetOfMainMoble
						,0
						,@GapHours
						,'M' --SurveyMode
						,@EquipmentType
						,@EquipmentSerNo
						,@INFTaskOutUID
						,@SrcDTLT
					)


				END

				IF @IsPicarro = 1 
				BEGIN

					Select @CurrentInspectionType = InspectionType from tInspectionRequest where InspectionRequestUID = @InspectionRequestUID
					
					IF @CurrentInspectionType = 'TR'
					BEGIN

						Select @Revision = Count(*) from tInspectionRequest where InspectionRequestUID = @InspectionRequestUID

						Update tInspectionRequest set ActiveFlag = 0 Where InspectionRequestUID = @InspectionRequestUID

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
							@UserUID,
							CreateDTLT,
							getdate(),
							Comments,
							RevisionComments,
							@Revision, -- Revision,
							1, --ActiveFlag,
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
							'PIC', --InspectionType,
							ActualStartDate
						from tInspectionRequest where InspectionRequestUID = @InspectionRequestUID and Revision = @Revision - 1

						Select @PICCount = Count(*) from [dbo].[tMapStampPicaro] where InspectionRequestUID = @InspectionRequestUID and ActiveFlag = 1

						If @PICCount < 3
						BEGIN

							While (3 - @PICCount - @InsertedPIC) > 0
							BEGIN

								select @NextID = IDENT_CURRENT('tMapStampPicaro') + 1

								Insert Into [dbo].[tMapStampPicaro]
								(
									MapStampPicaroUID,
									InspectionRequestUID,
									ProjectID,
									CreatedByUserUID,
									Seq
									
									
								)
								Values
								(
									[dbo].[CreateUID]('PICMapStamp', @NextID, 'System', getdate())
									,@InspectionRequestUID
									,1
									,'User_System_Automation'
									,@InsertedPIC + 1
								)

								Set @InsertedPIC = @InsertedPIC + 1

							END

						END








					END

				END





				--*/

				Fetch Next From objProcessingObj Into @ProcessingObjectID

			END
		
Close PlaceHolder
Deallocate PlaceHolder

Close objProcessingObj
Deallocate objProcessingObj

		
		

/*******************************************************

   Last thing we do
	Drop the table created in this proceedure

******************************************************/

Drop Table #JSON_Parse

SET NOCOUNT OFF
	
















