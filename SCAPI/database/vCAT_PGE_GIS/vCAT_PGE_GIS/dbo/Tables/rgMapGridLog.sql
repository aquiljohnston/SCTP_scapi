CREATE TABLE [dbo].[rgMapGridLog] (
    [rgMapGridID]         INT              IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [MapGridUID]          VARCHAR (100)    NOT NULL,
    [ProjectID]           INT              NOT NULL,
    [SourceID]            VARCHAR (50)     NOT NULL,
    [CreatedUserUID]      VARCHAR (100)    CONSTRAINT [DF_rgMapGridLogCreatedUserID] DEFAULT ((999)) NOT NULL,
    [ModifiedUserUID]     VARCHAR (100)    NOT NULL,
    [CreateDTLT]          DATETIME         CONSTRAINT [DF_rgMapGridLogCreateDTLT] DEFAULT (getdate()) NOT NULL,
    [ModifiedDTLT]        DATETIME         NOT NULL,
    [Comments]            VARCHAR (2000)   NULL,
    [RevisionComments]    VARCHAR (500)    NULL,
    [Revision]            INT              CONSTRAINT [DF_rgMapGridLogRevision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]          BIT              NOT NULL,
    [StatusType]          VARCHAR (200)    CONSTRAINT [DF_rgMapGridLogStatusType] DEFAULT ('Active') NOT NULL,
    [SAPObjectID]         VARCHAR (100)    NULL,
    [OfficeCode]          VARCHAR (200)    NULL,
    [FLOC]                VARCHAR (200)    NULL,
    [FuncLocMWC]          VARCHAR (4)      NULL,
    [FuncLocMapBoundary]  VARCHAR (4)      NULL,
    [FuncLocPlatSuffix]   CHAR (1)         NULL,
    [FuncLocMap]          VARCHAR (4)      NULL,
    [FuncLocPlat]         VARCHAR (4)      NULL,
    [FuncLocPlatPrefix]   CHAR (1)         NULL,
    [FuncLocPlatNo]       INT              NULL,
    [CentroidLat]         FLOAT (53)       NULL,
    [CentroidLong]        FLOAT (53)       NULL,
    [TotalVertices]       INT              NULL,
    [MaxDistance]         FLOAT (53)       NULL,
    [GeoBufferWithDrift]  INT              NULL,
    [LastLeakSurvey]      DATETIME         NULL,
    [ScheduledSurvey]     DATETIME         NULL,
    [LeakSurveyFrequency] VARCHAR (50)     NULL,
    [Last6MoSurveyDate1]  DATETIME         NULL,
    [Next6MoSurveyDate1]  DATETIME         NULL,
    [Last6MoSurveyDate2]  DATETIME         NULL,
    [Next6MoSurveyDate2]  DATETIME         NULL,
    [Last1YrSurveyDate]   DATETIME         NULL,
    [Next1YrSurveyDate]   DATETIME         NULL,
    [Last3YrSurveyDate]   DATETIME         NULL,
    [Next3YrSurveyDate]   DATETIME         NULL,
    [ObjectID]            INT              NULL,
    [SHAPE]               [sys].[geometry] NULL,
    CONSTRAINT [PK_rgMapGridLog] PRIMARY KEY CLUSTERED ([rgMapGridID] ASC)
);


GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-MapGridUID]
    ON [dbo].[rgMapGridLog]([MapGridUID] ASC);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'INF001 Data Feed from PGE', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'rgMapGridLog', @level2type = N'COLUMN', @level2name = N'rgMapGridID';

