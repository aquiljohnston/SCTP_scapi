CREATE TABLE [dbo].[tInspectionService] (
    [tInspectionServicesID]       INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [InspectionServicesUID]       VARCHAR (100)      NULL,
    [MasterLeakLogUID]            VARCHAR (100)      NULL,
    [MapGridUID]                  VARCHAR (100)      NULL,
    [InspectionRequestUID]        VARCHAR (100)      NULL,
    [InspectionEquipmentUID]      VARCHAR (100)      NULL,
    [ProjectID]                   INT                NULL,
    [SourceID]                    VARCHAR (100)      NULL,
    [CreatedUserUID]              VARCHAR (100)      NULL,
    [ModifiedUserUID]             VARCHAR (100)      NULL,
    [SrcDTLT]                     DATETIME           NULL,
    [SrvDTLT]                     DATETIME           CONSTRAINT [DF_t_InspecitonServices_SrvDTLT] DEFAULT (getdate()) NULL,
    [SrvDTLTOffset]               DATETIMEOFFSET (7) CONSTRAINT [DF_t_InspecitonServices_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [Comments]                    VARCHAR (2000)     NULL,
    [RevisionComments]            VARCHAR (500)      NULL,
    [Revision]                    INT                CONSTRAINT [DF_t_InspecitonServices_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]                  BIT                CONSTRAINT [DF_tInspectionService_ActiveFlag] DEFAULT ((1)) NULL,
    [StatusType]                  VARCHAR (100)      CONSTRAINT [DF_t_InspecitonServices_StatusType] DEFAULT ('Active') NULL,
    [EquipmentType]               VARCHAR (200)      NULL,
    [InstrumentType]              VARCHAR (200)      NULL,
    [SerialNumber]                VARCHAR (50)       NULL,
    [CalibrationLevel]            FLOAT (53)         NULL,
    [CalibrationVerificationFlag] BIT                NULL,
    [WindSpeedStart]              INT                NULL,
    [WindSpeedEnd]                INT                NULL,
    [EquipmentModeType]           VARCHAR (200)      NULL,
    [EstimatedFeet]               INT                CONSTRAINT [DF_t_InspecitonServices_EstFeet] DEFAULT ((0)) NULL,
    [EstimatedServices]           INT                CONSTRAINT [DF_t_InspecitonServices_EstServices] DEFAULT ((0)) NULL,
    [EstimatedHours]              FLOAT (53)         CONSTRAINT [DF_t_InspecitonServices_EstHours] DEFAULT ((0)) NULL,
    [ApprovedFlag]                BIT                NULL,
    [ApprovedByUserUID]           VARCHAR (100)      NULL,
    [ApprovedDTLT]                DATETIME           NULL,
    [SubmittedFlag]               BIT                NULL,
    [SubmittedStatusType]         VARCHAR (200)      NULL,
    [SubmittedUserUID]            VARCHAR (100)      NULL,
    [SubmittedDTLT]               DATETIME           NULL,
    [ResponseStatusType]          VARCHAR (200)      NULL,
    [Response]                    VARCHAR (500)      NULL,
    [ResponceErrorDescription]    VARCHAR (500)      NULL,
    [ResponseDTLT]                DATETIME           NULL,
    [CompletedFlag]               BIT                CONSTRAINT [DF_tInspectionService_CompletedFlag] DEFAULT ((0)) NULL,
    [CompletedDTLT]               DATETIME           NULL,
    [SurveyMode]                  VARCHAR (10)       NULL,
    [PlaceHolderFlag]             BIT                CONSTRAINT [DF_tInspectionService_PlaceHolderFlag] DEFAULT ((0)) NULL,
    [WindSpeedStartUID]           VARCHAR (100)      CONSTRAINT [DF_tInspectionService_WindSpeedStartUID] DEFAULT ('') NULL,
    [WindSpeedMidUID]             VARCHAR (100)      CONSTRAINT [DF_tInspectionService_WindSpeedMidUID] DEFAULT ('') NULL,
    [MapAreaNumber]               INT                CONSTRAINT [DF_tInspectionService_MapAreaNumber] DEFAULT ((0)) NULL,
    [LockedFlag]                  BIT                CONSTRAINT [DF_tInspectionService_LockedFlag] DEFAULT ((1)) NULL,
    [TaskOutUID]                  VARCHAR (200)      NULL,
    [CreateDateTime]              DATETIME           NULL,
    [IsNotUsed]                   BIT                CONSTRAINT [DF_tInspectionService_IsNotUsed] DEFAULT ((0)) NULL,
    CONSTRAINT [PK_t_InspecitonServices] PRIMARY KEY CLUSTERED ([tInspectionServicesID] ASC)
);









