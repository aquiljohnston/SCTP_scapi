









CREATE PROCEDURE [dbo].[spWebManagementJSON_TransferFLOC]
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
			,'TransferFLOC'
			,@JSON_Str
			)



/***************************************************************************

--  Create the unique function needed for the record type below this line

***************************************************************************/

		Declare 
			@MasterLeakLogUID varchar(100) 
			,@CurrentFLOC varchar(100)
			,@NewFLOC varchar(100)
			,@CurrentSurveyFreq varchar(20)
			,@NewSurveyFreq varchar(20)
			,@AdHocMode varchar(1)
			,@Date varchar(25)
			,@ReturnFlag bit = 0
			,@CurrentInspectionRequestUID varchar(100)
			,@CurrentAssetInspectionUID varchar(100)
			,@NewInspectionRequestUID varchar(100)
			,@NewAssetInspectionUID varchar(100)
			,@NewAssetUID varchar(100)
			,@NewMapGridUID varchar(100)
			,@NextID int
			,@Revision int
			,@UserUID varchar(100)
			,@AssetAddressUID varchar(100)
			,@UserName varchar(20)
			,@Wall varchar(20)
			,@Plat varchar(20)
			,@WorkCenter varchar(20)
			,@InspectionType varchar(20)
			,@AssetInspectionUID varchar(100)
			,@AssetUID varchar(100)
			,@ReturnVal bit = 1
			


		Select @MasterLeakLogUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'MasterLeakLogUID'), '')
		Select @CurrentFLOC = ISNULL((Select StringValue From #JSON_Parse Where Name = 'CurrentFLOC'), '')
		Select @NewFLOC = ISNULL((Select StringValue From #JSON_Parse Where Name = 'NewFLOC'), '')
		Select @CurrentSurveyFreq = ISNULL((Select StringValue From #JSON_Parse Where Name = 'CurrentSurveyFreq'), '')
		Select @NewSurveyFreq = ISNULL((Select StringValue From #JSON_Parse Where Name = 'NewSurveyFreq'), '')
		Select @AdHocMode = ISNULL((Select StringValue From #JSON_Parse Where Name = 'AdHocMode'), '')
		Select @Date = ISNULL((Select StringValue From #JSON_Parse Where Name = 'Date'), '')
		Select @UserUID = ISNULL((Select StringValue From #JSON_Parse Where Name = 'UserCreatedUID'), '')


		
BEGIN TRY

	BEGIN Transaction
		
		IF @AdHocMode = '1'
		BEGIN

			Select @UserName = UserName from UserTb where UserUID = @UserUID
			
			select @NextID = IDENT_CURRENT('tInspectionRequest') + 1

			Set @NewInspectionRequestUID = [dbo].[CreateUID]('IR', @NextID, @UserName, getdate())

			Select @NewMapGridUID = MapGridUID, @Wall = FuncLocMap, @Plat = FuncLocPlat, @WorkCenter = FuncLocMWC from rgMapGridLog where FLOC = @NewFLOC and ActiveFlag = 1
			
			IF EXISTS (Select * from tMapStampPicaro where InspectionRequestUID = @CurrentInspectionRequestUID)
			BEGIN
				Set @InspectionType = 'PIC'
			END
			ELSE
			BEGIN
				Set @InspectionType = 'TR'
			END

			Insert Into tInspectionRequest
			(
				InspectionRequestUID,
				MapGridUID,
				SourceID,
				CreatedUserUID,
				ModifiedUserUID,
				CreateDTLT,
				ModifiedDTLT,
				Comments,
				Revision,
				ActiveFlag,
				StatusType,
				PipelineType,
				SurveyType,
				MapID,
				Wall,
				Plat,
				MWC,
				FLOC,
				InspectionFrequencyType,
				ReturnFlag,
				ApprovedFlag,
				SubmittedFlag,
				ReturnedFlag,
				CompletedFlag,
				InspectionType,
				ActualStartDate,
				AdhocFlag
			)
			Values
			(
				@NewInspectionRequestUID,
				@NewMapGridUID,
				'WEB',
				@UserUID,
				@UserUID,
				getdate(),
				getdate(),
				'',
				0,
				1,
				'In Progress',
				'GD',
				@NewSurveyFreq,
				@Wall + '-' + @Plat,
				@Wall,
				@Plat,
				@WorkCenter,
				@NewFLOC,
				Left(@NewSurveyFreq, 1) + SUBSTRING(@NewSurveyFreq, charindex(' ', @NewSurveyFreq) + 1, 1),
				0,
				0,
				0,
				0,
				0,
				@InspectionType,
				getdate(),
				1
			)

			
			select @NextID = IDENT_CURRENT('[dbo].[tgAssetInspection]') + 1

			Select @AssetInspectionUID = [dbo].[CreateUID]('AssetInspection', @NextID, @UserName, getdate())

			select @AssetUID = AssetUID From [dbo].[tgAsset] where MapGridUID = @NewMapGridUID and ActiveFlag = 1
						
			Insert Into [dbo].[tgAssetInspection]
			(
				AssetInspectionUID, 
				AssetUID, 
				MapGridUID, 
				InspectionRequestUID, 
				ProjectID, 
				SourceID, 
				CreatedUserUID, 
				ModifiedUserUID, 
				Revision, 
				ActiveFlag, 
				StatusType
			)
			Values ( 
				@AssetInspectionUID,
				@AssetUID,
				@NewMapGridUID,
				@NewInspectionRequestUID,
				1,
				'System',
				@UserUID,
				@UserUID,
				0,
				1,
				'Active'
			)

		


		END
		ELSE
		BEGIN

			Select @NewInspectionRequestUID = InspectionRequestUID, @NewMapGridUID = MapGridUID
			from tInspectionRequest 
			where FLOC = @NewFLOC 
				and SurveyType = @NewSurveyFreq 
				and StatusType <> 'Completed' 
				and ActiveFlag = 1

			select @NewAssetInspectionUID = AssetInspectionUID, @NewAssetUID = AssetUID
			from tgAssetInspection 
			where InspectionRequestUID = @NewInspectionRequestUID 
				and ActiveFlag = 1

		END

			Select @CurrentInspectionRequestUID = InspectionRequestLogUID 
			from  tMasterLeakLog
			Where MasterLeakLogUID = @MasterLeakLogUID
				AND ActiveFlag = 1

			select @CurrentAssetInspectionUID = AssetInspectionUID 
			from tgAssetInspection 
			where InspectionRequestUID = @CurrentInspectionRequestUID 
				and ActiveFlag = 1


			Declare curAddress Cursor static
			FOR
			Select AssetAddressUID from tgAssetAddress where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1

			Open curAddress

			Fetch Next From curAddress into @AssetAddressUID

			While @@FETCH_STATUS = 0
			BEGIN

			--Move AOC

				Update  [dbo].[tgAssetAddressAOC] set ActiveFlag = 0
				where AssetAddressUID = @AssetAddressUID and ActiveFlag = 1

				Insert Into [dbo].[tgAssetAddressAOC]
				(
					AssetAddressAOCUID,
					AssetAddressUID,
					AssetInspectionUID,
					InspectionRequestUID,
					MapGridUID,
					ProjectID,
					SourceID,
					CreatedUserUID,
					ModifiedUserUID,
					SrcDTLT,
					--SrvDTLT,
					--SrvDTLTOffset,
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
					AOCType,
					AOCReasonType,
					AOCOther,
					Photo1,
					Photo2,
					Photo3,
					OptionalData1,
					OptionalData2,
					OptionalData3,
					OptionalData4,
					OptionalData5,
					ApprovedFlag,
					ApprovedByUserUID,
					ApprovedDTLT,
					SubmittedFlag,
					SubmittedStatusType,
					SubmittedUserUID,
					SubmittedDTLT,
					ResponseStatusType,
					Responsecomments,
					ResponceErrorComments,
					ResponseDTLT,
					CompletedFlag,
					CompletedDTLT,
					DateFound,
					MeterNumber,
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
					MasterLeakLogUID,
					ActivityUID					
				)
				Select 
					AOC.AssetAddressAOCUID,
					AssetAddressUID,
					@NewAssetInspectionUID, --AssetInspectionUID,
					@NewInspectionRequestUID, --InspectionRequestUID,
					@NewMapGridUID, --MapGridUID,
					ProjectID,
					'WEB', --SourceID,
					CreatedUserUID,
					@UserUID, --ModifiedUserUID,
					getdate(), --SrcDTLT,
					--SrvDTLT,
					--SrvDTLTOffset,
					SrcOpenDTLT,
					SrcClosedDTLT,
					GPSType,
					GPSSentence,
					Latitude,
					Longitude,
					SHAPE,
					Comments,
					'Transfered To New FLOC', --RevisionComments,
					NextRev.NextRevision, -- Revision,
					1, --ActiveFlag,
					StatusType,
					AOCType,
					AOCReasonType,
					AOCOther,
					Photo1,
					Photo2,
					Photo3,
					OptionalData1,
					OptionalData2,
					OptionalData3,
					OptionalData4,
					OptionalData5,
					ApprovedFlag,
					ApprovedByUserUID,
					ApprovedDTLT,
					SubmittedFlag,
					SubmittedStatusType,
					SubmittedUserUID,
					SubmittedDTLT,
					ResponseStatusType,
					Responsecomments,
					ResponceErrorComments,
					ResponseDTLT,
					CompletedFlag,
					CompletedDTLT,
					DateFound,
					MeterNumber,
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
					MasterLeakLogUID,
					ActivityUID					
				From [dbo].[tgAssetAddressAOC] AOC
				Join (select AssetAddressAOCUID, count(*) NextRevision 
				from [dbo].[tgAssetAddressAOC] 
				where AssetAddressUID = @AssetAddressUID Group By AssetAddressAOCUID) NextRev on nextrev.AssetAddressAOCUID = AOC.AssetAddressAOCUID and AOC.Revision = NextRev.NextRevision - 1

			--Move CGE

				Update  [dbo].[tgAssetAddressCGE] set ActiveFlag = 0
				where AssetAddressUID = @AssetAddressUID and ActiveFlag = 1

				Insert Into [dbo].[tgAssetAddressCGE]
				(
					AssetAddressCGEUID,
					AssetAddressUID,
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
					CGENIFType,
					CGEReasonType,
					NIFReasonType,
					CGECardFlag,
					CGECardNo,
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
					HeightOfGeoid,
					TimeSecondsSinceLastDGPS,
					ChecksumData,
					Bearing,
					Speed,
					GPSStatus,
					NumberOfGPSAttempts,
					InspectionRequestUID,
					ActivityUID
				)
				Select
					CGE.AssetAddressCGEUID,
					AssetAddressUID,
					@NewAssetInspectionUID, --AssetInspectionUID,
					MasterLeakLogUID,
					@NewMapGridUID, --MapGridUID,
					ProjectID,
					'WEB', --SourceID,
					CreatedUserUID,
					@UserUID, --ModifiedUserUID,
					SrcDTLT,
					--SrvDTLT,
					--SrvDTLTOffset,
					SrcOpenDTLT,
					SrcClosedDTLT,
					GPSType,
					GPSSentence,
					Latitude,
					Longitude,
					SHAPE,
					Comments,
					'Transfered To New FLOC', --RevisionComments,
					NextRev.NextRevision, -- Revision,
					1, --ActiveFlag,
					StatusType,
					CGENIFType,
					CGEReasonType,
					NIFReasonType,
					CGECardFlag,
					CGECardNo,
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
					HeightOfGeoid,
					TimeSecondsSinceLastDGPS,
					ChecksumData,
					Bearing,
					Speed,
					GPSStatus,
					NumberOfGPSAttempts,
					InspectionRequestUID,
					ActivityUID
				From [dbo].[tgAssetAddressCGE] CGE
				Join (select AssetAddressCGEUID, count(*) NextRevision 
				from [dbo].[tgAssetAddressCGE] 
				where AssetAddressUID = @AssetAddressUID Group By AssetAddressCGEUID) NextRev  on nextrev.AssetAddressCGEUID = CGE.AssetAddressCGEUID and CGE.Revision = NextRev.NextRevision - 1

			--Move Indication

				Update tgAssetAddressIndication set ActiveFlag = 0
				where AssetAddressUID = @AssetAddressUID and ActiveFlag = 1

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
					AssetAddressUID,
					@NewInspectionRequestUID, --InspectionRequestUID,
					@NewMapGridUID, --MapGridUID,
					MasterLeakLogUID,
					ProjectID,
					'WEB', --SourceID,
					CreatedUserUID,
					@UserUID, --ModifiedUserUID,
					@Date, -- SrcDTLT,
					SrcOpenDTLT,
					SrcClosedDTLT,
					GPSType,
					GPSSentence,
					Latitude,
					Longitude,
					SHAPE,
					Comments,
					'Transfered To New FLOC', --RevisionComments,
					NextRev.NextRevision, -- Revision,
					1, --ActiveFlag,
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
				From tgAssetAddressIndication aai
				Join (Select AssetAddressIndicationUID, count(*) NextRevision 
				from tgAssetAddressIndication
				where AssetAddressUID = @AssetAddressUID Group By AssetAddressIndicationUID) NextRev on aai.AssetAddressIndicationUID = NextRev.AssetAddressIndicationUID and aai.Revision = NextRev.NextRevision - 1

			--Move Inspections

				Update tgAssetAddressInspection set ActiveFlag = 0
				where AssetAddressUID = @AssetAddressUID and ActiveFlag = 1

				Insert Into tgAssetAddressInspection
				(
					AssetAddressInspectionUID,
					AssetAddressUID,
					AssetInspectionUID,
					MapGridUID,
					InspectionRequestUID,
					MasterLeakLogUID,
					CreatedUserUID,
					ModifiedUserUID,
					SourceID,
					InGridFlag,
					SrcDTLT,
					Revision,
					ActiveFlag,
					StatusType,
					Latitude,
					Longitude,
					GPSSource,
					GPSType,
					GPSSentence,
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
					ActivityUID,
					SrcOpenDTLT,
					ElectrolysisSurveyFlag,
					RiserPipeSoilMeas,
					DIMPSurveyFlag,
					DIMPRiserType,
					ServiceHeadAdapterType,
					ManifoldSetFlag,
					ServiceValueFlag,
					FilterFlag,
					FilterSize,
					FilterMfg,
					FilterModel,
					Regulator1Flag,
					Regulator1Size,
					Regulator1Mfg,
					Regulator1Model,
					Regulator2Flag,
					Regulator2Size,
					Regulator2Mfg,
					Regulator2Model,
					Regulator3Flag,
					Regulator3Size,
					Regulator3Mfg,
					Regulator3Model,
					MeterType,
					MeterMfg,
					MeterModel,
					ECFlag,
					AMRFlag,
					DripTankFlag,
					Photo1,
					Photo2,
					Photo3
				)
				Select
					aai.AssetAddressInspectionUID,
					AssetAddressUID,
					@NewAssetInspectionUID,
					@NewMapGridUID, --MapGridUID,
					@NewInspectionRequestUID,
					MasterLeakLogUID,
					CreatedUserUID,
					@UserUID, --ModifiedUserUID,
					'WEB', --SourceID,
					InGridFlag,
					getdate(), --SrcDTLT,
					NextRev.NextRevision, -- Revision,
					1, --ActiveFlag,
					StatusType,
					Latitude,
					Longitude,
					GPSSource,
					GPSType,
					GPSSentence,
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
					ActivityUID,
					SrcOpenDTLT,
					ElectrolysisSurveyFlag,
					RiserPipeSoilMeas,
					DIMPSurveyFlag,
					DIMPRiserType,
					ServiceHeadAdapterType,
					ManifoldSetFlag,
					ServiceValueFlag,
					FilterFlag,
					FilterSize,
					FilterMfg,
					FilterModel,
					Regulator1Flag,
					Regulator1Size,
					Regulator1Mfg,
					Regulator1Model,
					Regulator2Flag,
					Regulator2Size,
					Regulator2Mfg,
					Regulator2Model,
					Regulator3Flag,
					Regulator3Size,
					Regulator3Mfg,
					Regulator3Model,
					MeterType,
					MeterMfg,
					MeterModel,
					ECFlag,
					AMRFlag,
					DripTankFlag,
					Photo1,
					Photo2,
					Photo3
				From tgAssetAddressInspection aai
				Join (Select AssetAddressInspectionUID, count(*) NextRevision 
				from tgAssetAddressInspection
				where AssetAddressUID = @AssetAddressUID Group By AssetAddressInspectionUID)  NextRev on aai.AssetAddressInspectionUID = NextRev.AssetAddressInspectionUID and aai.Revision = NextRev.NextRevision - 1

			--Last Move the Address Record

				Update tgAssetAddress set ActiveFlag = 0
				where AssetAddressUID = @AssetAddressUID and ActiveFlag = 1

				Insert Into tgAssetAddress
				(
					AssetAddressUID,
					AssetUID,
					AssetInspectionUID,
					MapGridUID,
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
					MasterLeakLogUID,
					ActivityUID,
					SrcOpenDTLT,
					ReverseGeoLocationString,
					Grade1ReleaseReasonType,
					Grade1ReleaseDateTime
				)
				Select
					aa.AssetAddressUID,
					@NewAssetUID, --AssetUID,
					@NewAssetInspectionUID, --AssetInspectionUID,
					@NewMapGridUID, --MapGridUID,
					ProjectID,
					'WEB', --SourceID,
					CreatedUserUID,
					@UserUID, --ModifiedUserUID,
					@Date, -- SrcDTLT,
					GPSType,
					GPSSentence,
					Latitude,
					Longitude,
					SHAPE,
					Comments,
					'Transfered To New FLOC', --RevisionComments,
					NextRev.NextRevision, -- Revision,
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
					MasterLeakLogUID,
					ActivityUID,
					SrcOpenDTLT,
					ReverseGeoLocationString,
					Grade1ReleaseReasonType,
					Grade1ReleaseDateTime
				From tgAssetAddress aa
				Join (Select AssetAddressUID, count(*) NextRevision 
						from tgAssetAddress
						where AssetAddressUID = @AssetAddressUID 
						Group By AssetAddressUID) NextRev on aa.AssetAddressUID = NextRev.AssetAddressUID and aa.Revision = NextRev.NextRevision - 1


				Fetch Next From curAddress into @AssetAddressUID


			END

--All of the address information has been moved, now we need to move the MasterLeakLog, InspectionServices, and any PicaroMapStamp Information

			
			-- Move Master Leak Log

			Update  [dbo].[tMasterLeakLog] set ActiveFlag = 0
			where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1

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
				mll.MasterLeakLogUID,
				@NewInspectionRequestUID, --InspectionRequestLogUID,
				@NewMapGridUID, --MapGridUID,
				ServiceDate,
				ProjectID,
				'WEB', --SourceID,
				CreatedUserUID,
				@UserUID, -- ModifiedUserUID,
				@Date, -- SrcDTLT,
				Comments,
				'Transfered To New FLOC', --RevisionComments,
				NextRev.NextRevision, -- Revision,
				1, --ActiveFlag,
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
			From tMasterLeakLog mll
			Join (select MasterLeakLogUID, Count(*) NextRevision 
					from tMasterLeakLog
					where MasterLeakLogUID = @MasterLeakLogUID
					Group By MasterLeakLogUID) NextRev on mll.MasterLeakLogUID = Nextrev.MasterLeakLogUID and mll.Revision = NextRev.NextRevision - 1

			-- Move Inspection Services

			Update  [dbo].[tInspectionService] set ActiveFlag = 0
			where MasterLeakLogUID = @MasterLeakLogUID and ActiveFlag = 1

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
			select
				[is].InspectionServicesUID,
				MasterLeakLogUID,
				@NewMapGridUID, --MapGridUID,
				@NewInspectionRequestUID, --InspectionRequestUID,
				InspectionEquipmentUID,
				ProjectID,
				'WEB', --SourceID,
				CreatedUserUID,
				@UserUID, -- ModifiedUserUID,
				@Date, --SrcDTLT,
				Comments,
				'Transfered To New FLOC', --RevisionComments,
				NextRev.NextRevision, -- Revision,
				1, --ActiveFlag,
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
			from [dbo].[tInspectionService] [is]
			Join (select InspectionServicesUID, Count(*) NextRevision 
					from [dbo].[tInspectionService]
					where MasterLeakLogUID = @MasterLeakLogUID
					Group By InspectionServicesUID) NextRev on NextRev.InspectionServicesUID = [is].InspectionServicesUID and [is].Revision = NextRev.NextRevision - 1

			--Copy tMapStampPicaro if they exist in current IR Request and Not in Move To IR Request

			IF EXISTS (Select * from tMapStampPicaro where InspectionRequestUID = @CurrentInspectionRequestUID)
				AND NOT EXISTS (Select * from tMapStampPicaro where InspectionRequestUID = @NewInspectionRequestUID)
			BEGIN

				Insert Into tMapStampPicaro
				(
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
					Hours
				)
				Select
					@NewInspectionRequestUID,
					MapStampUID,
					ProjectID,
					@UserUID CreatedByUserUID,
					ModifiedByUserUID,
					@Date CreatedDateTime,
					ModifiedDateTime,
					0, --Revision,
					1, --ActiveFlag,
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
					Hours
				From tMapStampPicaro
				where InspectionRequestUID = @CurrentInspectionRequestUID and Revision = 0

				Update tMapStampPicaro 
				Set MapStampPicaroUID = [dbo].[CreateUID]('PICMapStamp', MapStampPicaroID, 'System', @Date)  
				where InspectionRequestUID = @NewAssetInspectionUID 
					and Revision = 0
					and MapStampPicaroUID is null


			END

		COMMIT TRANSACTION

	END TRY
	BEGIN CATCH
		ROLLBACK TRANSACTION
		set @ReturnVal = 0
	END CATCH
/*
tgAssetAddress
tgAssetAddressAOC
tgAssetAddressCGE
tgAssetAddressIndication
tgAssetAddressInspection

tInspectionService
tMasterLeakLog
tMapStampPicaro In		
*/		
		--END

/*******************************************************

   Last thing we do
	Drop the table created in this proceedure

******************************************************/

Drop Table #JSON_Parse

SET NOCOUNT OFF

--Return @ReturnVal

Select @ReturnVal As Succeeded