CREATE TABLE [dbo].[rMeter] (
    [rMeterID]         INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [MeterUID]         VARCHAR (100)  NOT NULL,
    [ProjectID]        INT            NOT NULL,
    [SourceID]         VARCHAR (50)   NOT NULL,
    [CreatedUserUID]   VARCHAR (100)  CONSTRAINT [DF_rMeterCreatedUserID] DEFAULT ((999)) NOT NULL,
    [ModifiedUserUID]  VARCHAR (100)  NOT NULL,
    [CreateDTLT]       DATETIME       CONSTRAINT [DF_rMeterCreateDTLT] DEFAULT (getdate()) NOT NULL,
    [ModifiedDTLT]     DATETIME       NOT NULL,
    [Comments]         VARCHAR (2000) NULL,
    [RevisionComments] VARCHAR (500)  NULL,
    [Revision]         INT            CONSTRAINT [DF_rMeterRevision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]       BIT            NOT NULL,
    [StatusType]       VARCHAR (200)  CONSTRAINT [DF_rMeterStatusType] DEFAULT ('Active') NOT NULL,
    [MeterType]        VARCHAR (200)  NULL,
    [MeterMfgType]     VARCHAR (200)  NULL,
    [MeterModelType]   VARCHAR (200)  NULL,
    CONSTRAINT [PK_rMeter] PRIMARY KEY CLUSTERED ([rMeterID] ASC)
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'INF000 Meter Data Feed from PGE', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'rMeter', @level2type = N'COLUMN', @level2name = N'rMeterID';

