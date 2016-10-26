CREATE TABLE [dbo].[tEquipmentMRP] (
    [EquipmentMRPID]          INT            IDENTITY (1, 1) NOT NULL,
    [EquipmentLogUID]         VARCHAR (100)  NULL,
    [SAPEquipmentType]        VARCHAR (50)   NULL,
    [SAPInstrumentType]       VARBINARY (50) NULL,
    [CreatedUserUID]          VARCHAR (100)  NULL,
    [ModifiedUserUID]         VARCHAR (100)  NULL,
    [CreatedDateTime]         DATETIME       CONSTRAINT [DF_tEquipmentMRP_CreatedDateTime] DEFAULT (getdate()) NULL,
    [ModifiedDateTime]        DATETIME       NULL,
    [SourceDateTime]          DATETIME       NULL,
    [Revision]                INT            CONSTRAINT [DF_tEquipmentMRP_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]              BIT            CONSTRAINT [DF_tEquipmentMRP_ActiveFlag] DEFAULT ((1)) NULL,
    [WorkCenterSupervisorUID] VARCHAR (100)  NULL,
    [Location]                VARCHAR (50)   NULL,
    [CatagoryType]            VARCHAR (200)  NULL,
    [DefectiveType]           VARCHAR (200)  NULL,
    [DefectiveOtherType]      VARCHAR (200)  NULL,
    [Manufacture]             VARCHAR (100)  NULL,
    [ManufactureType]         VARCHAR (200)  NULL,
    [EquipmentAge]            INT            NULL,
    [SafetyIssueType]         VARCHAR (200)  NULL,
    [ManufactureQuanity]      INT            CONSTRAINT [DF_tEquipmentMRP_ManufactureQuanity] DEFAULT ((1)) NULL,
    [CauseProblemType]        VARCHAR (200)  NULL,
    [MPRDescription]          VARCHAR (MAX)  NULL,
    [ApprovedFlag]            BIT            CONSTRAINT [DF_tEquipmentMRP_ApprovedFlag] DEFAULT ((0)) NULL,
    [ApprovedByUserUID]       VARCHAR (100)  NULL,
    [ApprovedDateTime]        DATETIME       NULL
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'Always 1', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'tEquipmentMRP', @level2type = N'COLUMN', @level2name = N'ManufactureQuanity';

