CREATE TABLE [dbo].[rFilter] (
    [rFilterID]        INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [FilterUID]        VARCHAR (100)  NOT NULL,
    [ProjectID]        INT            NOT NULL,
    [SourceID]         VARCHAR (50)   NOT NULL,
    [CreatedUserUID]   VARCHAR (100)  CONSTRAINT [DF_rFilterCreatedUserID] DEFAULT ((999)) NOT NULL,
    [ModifiedUserUID]  VARCHAR (100)  NOT NULL,
    [CreateDTLT]       DATETIME       CONSTRAINT [DF_rFilterCreateDTLT] DEFAULT (getdate()) NOT NULL,
    [ModifiedDTLT]     DATETIME       NOT NULL,
    [Comments]         VARCHAR (2000) NULL,
    [RevisionComments] VARCHAR (500)  NULL,
    [Revision]         INT            CONSTRAINT [DF_rFilterRevision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]       BIT            NOT NULL,
    [StatusType]       VARCHAR (200)  CONSTRAINT [DF_rFilterStatusType] DEFAULT ('Active') NOT NULL,
    [FilterSizeType]   VARCHAR (200)  NULL,
    [FilterMfgType]    VARCHAR (200)  NULL,
    [FilterModelType]  VARCHAR (200)  NULL,
    CONSTRAINT [PK_rFilter] PRIMARY KEY CLUSTERED ([rFilterID] ASC)
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'INF000 Filter Data Feed from PGE', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'rFilter', @level2type = N'COLUMN', @level2name = N'rFilterID';

