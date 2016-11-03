CREATE TABLE [dbo].[tInspectionsEquipment] (
    [InspecitonEquipmentID]       INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [InspecitonEquipmentUID]      VARCHAR (100)      NULL,
    [InspectorOQLogUID]           VARCHAR (100)      NULL,
    [EquipmentLogUID]             VARCHAR (100)      NULL,
    [ProjectID]                   INT                NULL,
    [SourceID]                    VARCHAR (100)      NULL,
    [CreatedUserUID]              VARCHAR (100)      NULL,
    [ModifiedUserUID]             VARCHAR (100)      NULL,
    [SrcDTLT]                     DATETIME           NULL,
    [SrvDTLT]                     DATETIME           CONSTRAINT [DF_t_InspecitonsEquipment_SrvDTLT] DEFAULT (getdate()) NULL,
    [SrvDTLTOffset]               DATETIMEOFFSET (7) CONSTRAINT [DF_t_InspecitonsEquipment_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [Comments]                    VARCHAR (2000)     NULL,
    [RevisionComments]            VARCHAR (500)      NULL,
    [Revision]                    INT                CONSTRAINT [DF_t_InspecitonsEquipment_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]                  BIT                CONSTRAINT [DF_tInspectionsEquipment_ActiveFlag] DEFAULT ((1)) NULL,
    [LastEquipmentDayFlag]        BIT                NULL,
    [EquipmentType]               VARCHAR (200)      NULL,
    [SerialNumber]                VARCHAR (50)       NULL,
    [ReadPPM]                     FLOAT (53)         NULL,
    [CalibrationVerificationFlag] BIT                NULL,
    [AlarmPPM]                    FLOAT (53)         NULL,
    [MPRFlag]                     BIT                CONSTRAINT [DF_tInspectionsEquipment_MPRFlag] DEFAULT ((0)) NULL,
    [PrNtfNo]                     VARCHAR (25)       NULL,
    [SAPEqID]                     VARCHAR (25)       NULL,
    [MWC]                         VARCHAR (25)       NULL,
    [CalbDate]                    DATETIME           NULL,
    [IsUsedToday]                 BIT                NULL,
    [MPRStatus]                   VARCHAR (10)       NULL,
    [SafetyIssue]                 VARCHAR (25)       NULL,
    [InstrumentAge]               VARCHAR (25)       NULL,
    [MasterLeakLogUID]            VARCHAR (100)      NULL,
    [StatusType]                  VARCHAR (200)      CONSTRAINT [DF_tInspectionsEquipment_StatusType] DEFAULT ('Active') NULL,
    [OMDExmQty]                   FLOAT (53)         NULL,
    [LaserCalb]                   BIT                NULL,
    [PLELRead]                    FLOAT (53)         NULL,
    [PGASRead]                    FLOAT (53)         NULL,
    [SCOPMethod]                  VARCHAR (50)       NULL,
    [StationPass]                 BIT                CONSTRAINT [DF_tInspectionsEquipment_StationPass] DEFAULT ((0)) NULL,
    CONSTRAINT [PK_t_InspecitonsEquipment] PRIMARY KEY CLUSTERED ([InspecitonEquipmentID] ASC)
);







