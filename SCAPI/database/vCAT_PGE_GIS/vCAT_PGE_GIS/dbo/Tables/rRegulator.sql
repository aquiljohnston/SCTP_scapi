CREATE TABLE [dbo].[rRegulator] (
    [rRegulatorID]       INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [RegulatorUID]       VARCHAR (100)  NOT NULL,
    [ProjectID]          INT            NOT NULL,
    [SourceID]           VARCHAR (50)   NOT NULL,
    [CreatedUserUID]     VARCHAR (100)  CONSTRAINT [DF_rRegulatorCreatedUserID] DEFAULT ((999)) NOT NULL,
    [ModifiedUserUID]    VARCHAR (100)  NOT NULL,
    [CreateDTLT]         DATETIME       CONSTRAINT [DF_rRegulatorCreateDTLT] DEFAULT (getdate()) NOT NULL,
    [ModifiedDTLT]       DATETIME       NOT NULL,
    [Comments]           VARCHAR (2000) NULL,
    [RevisionComments]   VARCHAR (500)  NULL,
    [Revision]           INT            CONSTRAINT [DF_rRegulatorRevision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]         BIT            NOT NULL,
    [StatusType]         VARCHAR (200)  CONSTRAINT [DF_rRegulatorStatusType] DEFAULT ('Active') NOT NULL,
    [RegulatorSizeType]  VARCHAR (200)  NULL,
    [RegulatorMfgType]   VARCHAR (200)  NULL,
    [RegulatorModelType] VARCHAR (200)  NULL,
    CONSTRAINT [PK_rRegulator] PRIMARY KEY CLUSTERED ([rRegulatorID] ASC)
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'INF000 Regulator Data Feed from PGE', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'rRegulator', @level2type = N'COLUMN', @level2name = N'rRegulatorID';

